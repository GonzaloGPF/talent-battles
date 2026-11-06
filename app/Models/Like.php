<?php

namespace App\Models;

use App\Interfaces\FilterableMorph;
use App\Interfaces\HasCreatedByInterface;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterableMorph;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperLike
 */
class Like extends Model implements FilterableMorph, HasCreatedByInterface
{
    use HasCreatedBy,
        HasFactory,
        IsFilterableMorph;

    public static function getMorphColumn(): string
    {
        return 'likeable';
    }

    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTargetUser(): ?User
    {
        return $this->likeable?->createdBy;
    }
}
