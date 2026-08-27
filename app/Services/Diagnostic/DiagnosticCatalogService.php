<?php

namespace App\Services\Diagnostic;

use App\Models\DiagnosticQuestion;
use App\Models\LearningObjective;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiagnosticCatalogService
{
    public function builderData(?int $subjectId): array
    {
        $subjects = Subject::orderBy('name')->get();
        $subject = $subjectId ? Subject::find($subjectId) : null;
        $objectives = $subject
            ? LearningObjective::with('children')
                ->where('subject_id', $subject->id)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get()
            : collect();
        $questions = $subject
            ? DiagnosticQuestion::with('options', 'learningObjective')
                ->where('subject_id', $subject->id)
                ->latest()
                ->get()
            : collect();

        return compact('subjects', 'subject', 'objectives', 'questions');
    }

    public function createObjective(array $data): LearningObjective
    {
        if (! empty($data['parent_id'])) {
            $parentBelongsToSubject = LearningObjective::whereKey($data['parent_id'])
                ->where('subject_id', $data['subject_id'])
                ->exists();

            if (! $parentBelongsToSubject) {
                throw ValidationException::withMessages([
                    'parent_id' => __('The parent objective must belong to the selected subject.'),
                ]);
            }
        }

        return LearningObjective::create($data);
    }

    public function createQuestion(array $data, int $correctOption): DiagnosticQuestion
    {
        $objectiveBelongsToSubject = LearningObjective::whereKey($data['learning_objective_id'])
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if (! $objectiveBelongsToSubject) {
            throw ValidationException::withMessages([
                'learning_objective_id' => __('The learning objective must belong to the selected subject.'),
            ]);
        }

        if (! array_key_exists($correctOption, $data['options'])) {
            throw ValidationException::withMessages([
                'correct_option' => __('Select a valid correct option.'),
            ]);
        }

        return DB::transaction(function () use ($data, $correctOption): DiagnosticQuestion {
            $question = DiagnosticQuestion::create([
                'subject_id' => $data['subject_id'],
                'learning_objective_id' => $data['learning_objective_id'],
                'question_text' => $data['question_text'],
                'type' => $data['type'],
            ]);

            foreach ($data['options'] as $index => $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'is_correct' => $index === $correctOption,
                ]);
            }

            return $question;
        });
    }

    public function deleteQuestion(DiagnosticQuestion $question): void
    {
        $question->delete();
    }
}
