<?php

namespace App\Services\Grade;

use App\Data\BulkGradeData;
use App\Data\GradeEntryData;
use App\Models\ExamType;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\Access\StudentRecordAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeEntryService
{
    public function __construct(
        private StudentRecordAccessService $access,
        private GradeService $grades,
    ) {}

    public function storeBulk(User $actor, BulkGradeData $data): int
    {
        foreach ($data->entries as $index => $row) {
            if ($row->score > $row->maxScore) {
                throw ValidationException::withMessages([
                    "grades.$index.score" => 'Score cannot exceed max_score.',
                ]);
            }

            $this->assertCanEnterGrade(
                $actor,
                $row->subjectId,
                $row->studentId,
            );
        }

        DB::transaction(function () use ($actor, $data): void {
            $tuples = [];

            foreach ($data->entries as $row) {
                $examType = ExamType::findOrFail($row->examTypeId);

                StudentGrade::updateOrCreate(
                    [
                        'student_user_id' => $row->studentId,
                        'subject_id' => $row->subjectId,
                        'exam_type_id' => $row->examTypeId,
                    ],
                    [
                        'semester_id' => $examType->semester_id,
                        'teacher_user_id' => $actor->id,
                        'score' => $row->score,
                        'max_score' => $row->maxScore,
                    ],
                );

                $tuples[] = [
                    'student_user_id' => $row->studentId,
                    'subject_id' => $row->subjectId,
                    'semester_id' => $examType->semester_id,
                ];
            }

            $this->grades->refreshSummaries($tuples);
        });

        $this->notifyGradeRecipients($data);

        return count($data->entries);
    }

    private function notifyGradeRecipients(BulkGradeData $data): void
    {
        $students = User::with('parents')
            ->whereIn('id', collect($data->entries)->pluck('studentId')->unique())
            ->get()
            ->keyBy('id');
        $subjects = Subject::whereIn('id', collect($data->entries)->pluck('subjectId')->unique())
            ->pluck('name', 'id');

        foreach ($data->entries as $row) {
            $student = $students->get($row->studentId);
            if (! $student) {
                continue;
            }

            $subjectName = $subjects->get($row->subjectId, 'a subject');

            $student->notify(new SystemNotification(
                'New grade available',
                'A new grade is available for :student in :subject.',
                route('student.results'),
                'grade',
                [
                    'student' => $student->name,
                    'subject' => $subjectName,
                ],
            ));

            foreach ($student->parents as $parent) {
                $parent->notify(new SystemNotification(
                    'New grade available',
                    'A new grade is available for :student in :subject.',
                    route('parent.results', ['child_id' => $student->id]),
                    'grade',
                    [
                        'student' => $student->name,
                        'subject' => $subjectName,
                    ],
                ));
            }
        }
    }

    public function storeWeb(
        User $teacher,
        int $subjectId,
        int $examTypeId,
        float $maxScore,
        array $scores,
    ): int {
        $rows = [];

        foreach ($scores as $studentId => $score) {
            if ($score === null || $score === '') {
                continue;
            }

            $rows[] = [
                'student_id' => (int) $studentId,
                'subject_id' => $subjectId,
                'exam_type_id' => $examTypeId,
                'score' => $score,
                'max_score' => $maxScore,
            ];
        }

        return $this->storeBulk(
            $teacher,
            new BulkGradeData(array_map(
                fn (array $row): GradeEntryData => GradeEntryData::fromArray($row),
                $rows,
            )),
        );
    }

    private function assertCanEnterGrade(User $actor, int $subjectId, int $studentId): void
    {
        $this->access->assertTeacherCanView($actor, $studentId, $subjectId);
    }
}
