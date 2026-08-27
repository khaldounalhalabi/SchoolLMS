<?php

namespace App\Enums;

enum DiagnosticQuestionType: string
{
    case MCQ = 'mcq';
    case TRUE_FALSE = 'true_false';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
