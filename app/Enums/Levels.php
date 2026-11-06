<?php

namespace App\Enums;

enum Levels: string
{
    case NOVICE = 'novice';
    case APPRENTICE = 'apprentice';
    case MEDIUM = 'medium';
    case ADVANCED = 'advanced';
    case EXPERT = 'expert';

    public function getVoteValue(): int
    {
        return match ($this) {
            Levels::NOVICE => 1,
            Levels::APPRENTICE => 2,
            Levels::MEDIUM => 3,
            Levels::ADVANCED => 4,
            Levels::EXPERT => 5,
        };
    }
}
