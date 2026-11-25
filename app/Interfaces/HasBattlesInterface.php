<?php

namespace App\Interfaces;

use App\Models\Battle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasBattlesInterface
{
    /**
     * @return MorphMany<Battle, covariant Model>
     */
    public function victories(): MorphMany;

    /**
     * @return BelongsToMany<Battle, covariant Model>
     */
    public function battles(): BelongsToMany;

    /**
     * @return BelongsToMany<Battle, covariant Model>
     */
    public function openBattle(): BelongsToMany;

    /**
     * @return BelongsToMany<Battle, covariant Model>
     */
    public function currentBattle(): BelongsToMany;

    public function getTotalPoints(int $battleId): float;
}
