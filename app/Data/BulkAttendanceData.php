<?php

namespace App\Data;

final readonly class BulkAttendanceData
{
    /** @param list<AttendanceEntryData> $entries */
    public function __construct(
        public int $classroomId,
        public string $date,
        public ?int $scheduleSlotId,
        public array $entries,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            classroomId: (int) $data['classroom_id'],
            date: $data['date'],
            scheduleSlotId: isset($data['schedule_slot_id'])
                ? (int) $data['schedule_slot_id']
                : null,
            entries: array_map(
                fn (array $entry): AttendanceEntryData => AttendanceEntryData::fromArray($entry),
                $data['entries'],
            ),
        );
    }
}
