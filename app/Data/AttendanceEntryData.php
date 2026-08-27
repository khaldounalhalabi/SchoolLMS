<?php

namespace App\Data;

use App\Enums\AttendanceStatus;

final readonly class AttendanceEntryData
{
    public function __construct(
        public int $studentId,
        public AttendanceStatus $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            studentId: (int) $data['student_id'],
            status: $data['status'] instanceof AttendanceStatus
                ? $data['status']
                : AttendanceStatus::from($data['status']),
        );
    }
}
