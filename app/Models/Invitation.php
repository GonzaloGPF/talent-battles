<?php

namespace App\Models;

use App\Interfaces\HasCreatedByInterface;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperInvitation
 */
class Invitation extends Model implements HasCreatedByInterface
{
    use HasCreatedBy,
        HasFactory,
        IsFilterable,
        SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
