<?php

namespace App\Enums;

enum Currency: string
{
    case USD = 'usd';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
