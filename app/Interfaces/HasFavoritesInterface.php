<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

/**
 * @property Collection $favorites
 */
interface HasFavoritesInterface
{
    public function favorites(): MorphMany;

    public function favorite(): MorphOne;
}
