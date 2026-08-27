<?php

namespace App\Data;

final readonly class GradeEntryData
{
    public function __construct(
        public int $studentId,
        public int $subjectId,
        public int $examTypeId,
        public float $score,
        public float $maxScore,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            studentId: (int) $data['student_id'],
            subjectId: (int) $data['subject_id'],
            examTypeId: (int) $data['exam_type_id'],
            score: (float) $data['score'],
            maxScore: (float) $data['max_score'],
        );
    }
}
