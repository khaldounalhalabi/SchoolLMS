<?php

namespace App\Data;

final readonly class DiagnosticSubmissionData
{
    /** @param list<DiagnosticAnswerData> $answers */
    public function __construct(public array $answers) {}

    public static function fromArray(array $data): self
    {
        return new self(
            answers: array_map(
                fn (array $answer): DiagnosticAnswerData => DiagnosticAnswerData::fromArray($answer),
                $data['answers'],
            ),
        );
    }
}
