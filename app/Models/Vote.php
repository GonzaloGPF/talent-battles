<?php

namespace App\Models;

use App\Interfaces\HasCreatedByInterface;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperVote
 */
class Vote extends Model implements HasCreatedByInterface
{
    use HasCreatedBy,
        HasFactory;

    protected $casts = [
        'battle_id' => 'int',
        'votable_id' => 'int',
        'value' => 'int',
    ];

    public function votable(): MorphTo
    {
        return $this->morphTo('votable');
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }
}
