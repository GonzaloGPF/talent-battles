<?php

namespace App\Interfaces;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

/**
 * @property Collection<int, Comment> $comments
 */
interface HasCommentsInterface
{
    /**
     * @return MorphMany<Comment, covariant Model>|HasMany<Comment, covariant Model>
     */
    public function comments(): MorphMany|HasMany;

    /**
     * @return MorphOne<Comment, covariant Model>|BelongsTo<Comment, covariant Model>
     */
    public function comment(): MorphOne|BelongsTo;
}
