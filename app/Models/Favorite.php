<?php

namespace App\Models;

use App\Interfaces\HasCreatedByInterface;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Database\Factories\FavoriteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperFavorite
 */
class Favorite extends Model implements HasCreatedByInterface
{
    /** @use HasFactory<FavoriteFactory> */
    use HasCreatedBy,
        HasFactory,
        IsFilterable;

    protected $fillable = ['favorite_list_id'];

    protected $casts = [
        'favorite_list_id' => 'int',
    ];

    public static function getMorphColumn(): string
    {
        return 'favoritable';
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<FavoriteList, $this>
     */
    public function favoriteList(): BelongsTo
    {
        return $this->belongsTo(FavoriteList::class);
    }

    public function getTargetUser(): ?User
    {
        return $this->favoritable->getRelationValue('createdBy');
    }
}
