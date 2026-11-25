<?php

namespace App\Interfaces;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

/**
 * @property Collection<int, Favorite> $favorites
 */
interface HasFavoritesInterface
{
    /**
     * @return MorphMany<Favorite, covariant Model>
     */
    public function favorites(): MorphMany;

    /**
     * @return MorphOne<Favorite, covariant Model>
     */
    public function favorite(): MorphOne;
}
