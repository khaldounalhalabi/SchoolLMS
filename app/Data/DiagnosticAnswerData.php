<?php

namespace App\Data;

final readonly class DiagnosticAnswerData
{
    public function __construct(
        public int $questionId,
        public ?int $selectedOptionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            questionId: (int) $data['question_id'],
            selectedOptionId: isset($data['selected_option_id'])
                ? (int) $data['selected_option_id']
                : null,
        );
    }
}
