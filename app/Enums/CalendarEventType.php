<?php

namespace App\Enums;

enum CalendarEventType: string
{
    case HOLIDAY = 'holiday';
    case EVENT = 'event';
    case EXAM = 'exam';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
