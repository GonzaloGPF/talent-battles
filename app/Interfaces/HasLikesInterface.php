<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

/**
 * @property Collection $likes
 */
interface HasLikesInterface
{
    public function likes(): MorphMany;

    public function like(): MorphOne;
}
