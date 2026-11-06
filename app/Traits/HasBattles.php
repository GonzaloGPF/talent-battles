<?php

namespace App\Traits;

use App\Enums\BattleStates;
use App\Models\Battle;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasBattles
{
    public function victories(): MorphMany
    {
        return $this->morphMany(Battle::class, 'winner');
    }

    public function battles(): BelongsToMany
    {
        return $this->belongsToMany(Battle::class);
    }

    public function openBattle(): BelongsToMany
    {
        return $this->battles()
            ->where('battle_state', BattleStates::OPEN);
        // ->limit(1) for some reason, this is dangerous. It avoids eager loading relationship when using ApiResource::firstManyToMany()
    }

    public function currentBattle(): BelongsToMany
    {
        return $this->battles()
            ->where('battle_state', BattleStates::IN_PROGRESS);
        // ->limit(1) for some reason, this is dangerous. It avoids eager loading relationship when using ApiResource::firstManyToMany()
    }

    public function getTotalPoints(int $battleId): float
    {
        return $this->votes
            ->where('battle_id', $battleId)
            ->sum('value') ?? 0;
    }
}
