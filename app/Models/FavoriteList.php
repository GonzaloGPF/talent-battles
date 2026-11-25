<?php

namespace App\Models;

use App\Interfaces\HasCreatedByInterface;
use App\Scopes\ViewOnlyMine;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use App\Traits\NameFormatter;
use Database\Factories\FavoriteListFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperFavoriteList
 */
class FavoriteList extends Model implements HasCreatedByInterface
{
    /** @use HasFactory<FavoriteListFactory> */
    use HasCreatedBy,
        HasFactory,
        IsFilterable,
        NameFormatter;

    protected $fillable = ['name'];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ViewOnlyMine);
        static::addGlobalScope(fn (Builder $builder) => $builder->withCount('favorites'));
    }

    /**
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
