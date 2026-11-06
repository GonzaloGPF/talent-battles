<?php

namespace App\Models;

use App\Interfaces\HasCreatedByInterface;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperFavorite
 */
class Favorite extends Model implements HasCreatedByInterface
{
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

    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    public function favoriteList(): BelongsTo
    {
        return $this->belongsTo(FavoriteList::class);
    }

    public function getTargetUser(): ?User
    {
        return $this->favoritable->createdBy;
    }
}
