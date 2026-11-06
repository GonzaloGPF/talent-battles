<?php

namespace App\Traits;

use App\Enums\DesignTypes;
use App\Enums\TrackTypes;
use BackedEnum;
use Illuminate\Support\Str;

trait EnumFromValue
{
    public static function fromValue(string $value): TrackTypes|DesignTypes|null
    {
        return collect(self::cases())
            ->first(fn (BackedEnum $enum) => $enum->name === Str::upper($value));
    }
}
