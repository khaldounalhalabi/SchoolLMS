<?php

namespace App\Services\Grade;

use App\Models\ExamType;
use App\Models\GradeSummary;
use App\Models\StudentGrade;

class GradeService
{
    private const LETTER_THRESHOLDS = [
        'A' => 90,
        'B' => 80,
        'C' => 70,
        'D' => 60,
    ];

    public function letterGrade(float $pct): string
    {
        foreach (self::LETTER_THRESHOLDS as $letter => $min) {
            if ($pct >= $min) {
                return $letter;
            }
        }

        return 'F';
    }

    /**
     * Recompute grade_summaries for a set of (student, subject, semester) tuples.
     * Call inside the same DB transaction as the grade inserts.
     */
    public function refreshSummaries(array $tuples): void
    {
        // $tuples = [['student_user_id' => X, 'subject_id' => Y, 'semester_id' => Z], ...]
        $unique = collect($tuples)->unique(
            fn (array $tuple): string => "{$tuple['student_user_id']}-{$tuple['subject_id']}-{$tuple['semester_id']}"
        );

        foreach ($unique as $tuple) {
            [
                'student_user_id' => $studentId,
                'subject_id' => $subjectId,
                'semester_id' => $semesterId,
            ] = $tuple;

            $examTypes = ExamType::where('semester_id', $semesterId)->get()->keyBy('id');
            $totalWeight = $examTypes->sum('weight_percent');

            if ($totalWeight == 0) {
                continue;
            }

            // $tuples = [['student_user_id' => X, 'subject_id' => Y, 'semester_id' => Z], ...]
            $grades = StudentGrade::where('student_user_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('semester_id', $semesterId)
                ->get();

            $weighted = 0;
            foreach ($grades as $grade) {
                $examType = $examTypes->get($grade->exam_type_id);
                if (! $examType || $grade->max_score == 0) {
                    continue;
                }

                $pct = ($grade->score / $grade->max_score) * 100;
                $weighted += $pct * ($examType->weight_percent / $totalWeight);
            }

            GradeSummary::updateOrCreate(
                ['student_user_id' => $studentId, 'subject_id' => $subjectId, 'semester_id' => $semesterId],
                ['weighted_average' => round($weighted, 2), 'letter_grade' => $this->letterGrade($weighted)]
            );
        }
    }
}
