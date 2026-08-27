<?php

namespace App\Enums;

enum Weekday: string
{
    case SUNDAY = 'sunday';
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
