<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasBattlesInterface
{
    public function victories(): MorphMany;

    public function battles(): BelongsToMany;

    public function openBattle(): BelongsToMany;

    public function currentBattle(): BelongsToMany;

    public function getTotalPoints(int $battleId): float;
}
