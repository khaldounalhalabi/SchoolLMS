<?php

namespace App\Domain;

enum MasteryLevel
{
    case Mastered;
    case Developing;
    case NeedsWork;
    case NotStarted;

    public static function fromPercent(?float $pct): self
    {
        if ($pct === null) {
            return self::NotStarted;
        }
        if ($pct >= 70) {
            return self::Mastered;
        }
        if ($pct >= 40) {
            return self::Developing;
        }
        return self::NeedsWork;
    }

    public function color(): string
    {
        return match ($this) {
            self::Mastered    => 'mastery-green',
            self::Developing  => 'mastery-yellow',
            self::NeedsWork   => 'mastery-red',
            self::NotStarted  => 'mastery-grey',
        };
    }

    public function label(?float $pct): string
    {
        if ($this === self::NotStarted) {
            return '—';
        }
        return $pct . '%';
    }
}
