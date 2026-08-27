<?php

namespace App\Data;

final readonly class BulkGradeData
{
    /** @param list<GradeEntryData> $entries */
    public function __construct(public array $entries) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entries: array_map(
                fn (array $entry): GradeEntryData => GradeEntryData::fromArray($entry),
                $data['grades'],
            ),
        );
    }
}
