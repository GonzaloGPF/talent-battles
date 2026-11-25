<?php

namespace App\Models;

use App\Scopes\NameSortingScope;
use App\Traits\IsFilterable;
use App\Traits\NameFormatter;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCountry
 */
class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory,
        IsFilterable,
        NameFormatter;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new NameSortingScope);
    }
}
