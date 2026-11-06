<?php

namespace App\Models;

use App\Scopes\NameSortingScope;
use App\Traits\IsFilterable;
use App\Traits\NameFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperRegion
 */
class Region extends Model
{
    use HasFactory,
        IsFilterable,
        NameFormatter;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new NameSortingScope);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
