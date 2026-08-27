<?php

namespace App\Enums;

enum FamilyRelation: string
{
    case FATHER = 'father';
    case MOTHER = 'mother';
    case GUARDIAN = 'guardian';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
