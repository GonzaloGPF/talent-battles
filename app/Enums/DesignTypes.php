<?php

namespace App\Enums;

use App\Interfaces\HasColorsInterface;
use App\Traits\EnumFromValue;

enum DesignTypes: string implements HasColorsInterface
{
    use EnumFromValue;

    case FREE = 'free';
    case LOGO = 'logo';
    case FONT = 'font';
    case PORTRAIT = 'portrait';
    case PHOTO = 'photo';
    case LANDSCAPE = 'landscape';
    case CHARACTER = 'character';

    public function getColor(): string
    {
        return match ($this) {
            self::FREE => '#f9c80e',
            self::LOGO => '#f86624',
            self::FONT => '#ea3546',
            self::PORTRAIT => '#662e9b',
            self::PHOTO => '#43bccd',
            self::LANDSCAPE => '#20bf55',
            self::CHARACTER => '#284b63',
        };
    }
}
