<?php

namespace App\Interfaces;

use App\Models\Like;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

/**
 * @property Collection<int, Like> $likes
 */
interface HasLikesInterface
{
    /**
     * @return MorphMany<Like, covariant Model>
     */
    public function likes(): MorphMany;

    /**
     * @return MorphOne<Like, covariant Model>
     */
    public function like(): MorphOne;
}
