<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait HasCreatedBy
 */
trait HasCreatedBy
{
    public function initializeHasCreatedBy(): void
    {
        $this->casts += ['created_by' => 'int'];
    }

    /**
     * A model created while someone is signed in belongs to them, unless it already says who created it.
     */
    public static function bootHasCreatedBy(): void
    {
        static::creating(function (self $model) {
            $model->created_by ??= auth()->id();
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
