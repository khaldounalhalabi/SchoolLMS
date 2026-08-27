<?php

namespace App\Services\Diagnostic;

use App\Data\DiagnosticSubmissionData;
use App\Models\DiagnosticAnswer;
use App\Models\DiagnosticAttempt;
use App\Models\DiagnosticQuestion;
use App\Models\KnowledgeMapResult;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiagnosticAttemptService
{
    public function testData(User $student, ?int $subjectId): array
    {
        $subjects = Subject::orderBy('name')->get();
        $subject = $subjectId ? Subject::find($subjectId) : null;
        $attempt = $subject ? $this->activeForSubject($student, $subject->id) : null;
        $questions = $attempt ? $this->questionsFor($attempt) : collect();
        $totalForSubject = $subject
            ? DiagnosticQuestion::where('subject_id', $subject->id)->count()
            : 0;
        $answered = $attempt ? $attempt->answers()->count() : 0;

        return compact('subjects', 'subject', 'attempt', 'questions', 'totalForSubject', 'answered');
    }

    public function start(User $student, int $subjectId): DiagnosticAttempt
    {
        Subject::findOrFail($subjectId);

        return DB::transaction(function () use ($student, $subjectId): DiagnosticAttempt {
            DiagnosticAttempt::where('student_user_id', $student->id)
                ->where('subject_id', $subjectId)
                ->whereNull('completed_at')
                ->update(['completed_at' => now()]);

            return DiagnosticAttempt::create([
                'student_user_id' => $student->id,
                'subject_id' => $subjectId,
                'started_at' => now(),
            ]);
        });
    }

    public function activeForSubject(User $student, int $subjectId): ?DiagnosticAttempt
    {
        return DiagnosticAttempt::where('student_user_id', $student->id)
            ->where('subject_id', $subjectId)
            ->whereNull('completed_at')
            ->latest()
            ->first();
    }

    public function forStudent(User $student, int $attemptId, bool $open = false): DiagnosticAttempt
    {
        return DiagnosticAttempt::whereKey($attemptId)
            ->where('student_user_id', $student->id)
            ->when($open, fn ($query) => $query->whereNull('completed_at'))
            ->firstOrFail();
    }

    public function questionsFor(DiagnosticAttempt $attempt): Collection
    {
        $answeredIds = $attempt->answers()->pluck('question_id');

        return DiagnosticQuestion::with('options')
            ->where('subject_id', $attempt->subject_id)
            ->whereNotIn('id', $answeredIds)
            ->limit(20)
            ->get();
    }

    public function submitAnswers(DiagnosticAttempt $attempt, DiagnosticSubmissionData $data): array
    {
        if ($attempt->completed_at) {
            throw ValidationException::withMessages([
                'attempt' => 'This diagnostic attempt has already been completed.',
            ]);
        }

        $questionIds = collect($data->answers)->pluck('questionId');
        if ($questionIds->count() !== $questionIds->unique()->count()) {
            throw ValidationException::withMessages([
                'answers' => 'Each question may only be submitted once.',
            ]);
        }

        $questions = DiagnosticQuestion::with('options')
            ->where('subject_id', $attempt->subject_id)
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        if ($questions->count() !== $questionIds->unique()->count()) {
            throw ValidationException::withMessages([
                'answers' => 'One or more questions do not belong to this diagnostic attempt.',
            ]);
        }

        $answeredIds = $attempt->answers()
            ->whereIn('question_id', $questionIds)
            ->pluck('question_id');

        if ($answeredIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'answers' => 'One or more questions have already been answered.',
            ]);
        }

        $masteryByObjective = [];

        DB::transaction(function () use ($attempt, $data, $questions, &$masteryByObjective): void {
            foreach ($data->answers as $answer) {
                $question = $questions->get($answer->questionId);
                $selectedOptionId = $answer->selectedOptionId;

                if ($selectedOptionId !== null && ! $question->options->contains('id', $selectedOptionId)) {
                    throw ValidationException::withMessages([
                        'answers' => 'One or more selected options do not belong to their questions.',
                    ]);
                }

                $isCorrect = $selectedOptionId !== null
                    && (bool) $question->options
                        ->firstWhere('id', $selectedOptionId)
                        ?->is_correct;

                DiagnosticAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_option_id' => $selectedOptionId,
                    'is_correct' => $isCorrect,
                ]);
            }

            $attempt->update(['completed_at' => now()]);
            $savedAnswers = $attempt->answers()->with('question')->get();
            $byObjective = $savedAnswers->groupBy('question.learning_objective_id');

            foreach ($byObjective as $objectiveId => $objectiveAnswers) {
                $total = $objectiveAnswers->count();
                $correct = $objectiveAnswers->where('is_correct', true)->count();
                $mastery = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

                KnowledgeMapResult::updateOrCreate(
                    [
                        'student_user_id' => $attempt->student_user_id,
                        'learning_objective_id' => $objectiveId,
                    ],
                    [
                        'mastery_percent' => $mastery,
                        'last_assessed_at' => now(),
                    ],
                );

                $masteryByObjective[$objectiveId] = $mastery;
            }
        });

        return $masteryByObjective;
    }
}
