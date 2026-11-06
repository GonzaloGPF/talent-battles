<?php

namespace App\Enums;

use App\Interfaces\HasColorsInterface;
use App\Traits\EnumFromValue;

enum TrackTypes: string implements HasColorsInterface
{
    use EnumFromValue;

    case BEAT = 'beat';
    case SAMPLE = 'sample';
    case SONG = 'song';

    public function getColor(): string
    {
        return match ($this) {
            self::BEAT => '#1abc9c',
            self::SAMPLE => '#f1c40f',
            self::SONG => '#9C27B0',
        };
    }
}
