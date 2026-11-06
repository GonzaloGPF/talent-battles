<?php

namespace App\Enums;

enum ReportStates: string
{
    case OPEN = 'open';
    case WORKING = 'working';
    case SOLVED = 'solved';
    case CLOSED = 'closed';

    public static function getPendingStates(): array
    {
        return [
            self::OPEN->value,
            self::WORKING->value,
        ];
    }

    public static function getFinishedStates(): array
    {
        return [
            self::SOLVED->value,
            self::CLOSED->value,
        ];
    }
}
