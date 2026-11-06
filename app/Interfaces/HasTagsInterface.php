<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property null|array[] $tags
 */
interface HasTagsInterface
{
    //    /**
    //     * @return BelongsToMany
    //     */
    //    public function tags(): BelongsToMany;

    public function scopeByTags(Builder $builder, array|string $values): Builder;
}
