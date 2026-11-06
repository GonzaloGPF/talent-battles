<?php

namespace App\Models;

use App\Enums\ActivityTypes;
use App\Interfaces\HasCreatedByInterface;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperActivity
 */
class Activity extends Model implements HasCreatedByInterface
{
    use HasCreatedBy,
        HasFactory,
        IsFilterable;

    protected $casts = [
        'receiver_id' => 'int',
        'activity_type' => ActivityTypes::class,
        'target_id' => 'int',
    ];

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
