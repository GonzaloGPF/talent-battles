<?php

namespace App\Services;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Facades\Logger;
use App\Jobs\CheckBattlesToFinish;
use App\Jobs\MatchBattles;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class BattleService
{
    /**
     * Minutes between two refreshes of the battles.
     */
    private const REFRESH_MINUTES = 10;

    /**
     * Production runs no scheduler, so battles are refreshed when someone looks at them: expired ones are finished and
     * new matchups take their places. It happens after the response is sent, and at most once every few minutes.
     */
    public function refreshBattlesAfterResponse(): void
    {
        if (Cache::add('battles.refresh', true, now()->addMinutes(self::REFRESH_MINUTES))) {
            CheckBattlesToFinish::dispatchAfterResponse();
            MatchBattles::dispatchAfterResponse();
        }
    }

    public function createForTracks(Track $trackA, Track $trackB): Battle
    {
        /** @var ?Battle $battle */
        $battle = $trackB->battles()->firstWhere('battle_state', BattleStates::OPEN);

        if (! $battle) {
            $battle = Battle::forceCreate([
                'created_by' => auth()->id(),
                'battle_state' => BattleStates::OPEN,
                'battle_type' => BattleTypes::TRACK,
            ]);
        }

        $battle->tracks()->syncWithoutDetaching([$trackA->id, $trackB->id]);

        $this->changeStateToInProgress($battle);

        return $battle->fresh();
    }

    public function createForDesigns(Design $designA, Design $designB): Battle
    {
        /** @var ?Battle $battle */
        $battle = $designB->battles()->firstWhere('battle_state', BattleStates::OPEN);

        if (! $battle) {
            $battle = Battle::forceCreate([
                'created_by' => auth()->id(),
                'battle_state' => BattleStates::OPEN,
                'battle_type' => BattleTypes::DESIGN,
            ]);
        }

        $battle->designs()->syncWithoutDetaching([$designA->id, $designB->id]);

        $this->changeStateToInProgress($battle);

        return $battle;
    }

    public function createForTrack(Track $track): Battle
    {
        /** @var Battle $battle */
        $battle = Battle::where('battle_state', BattleStates::OPEN)
            ->where(function (Builder $builder) use ($track) {
                $builder->whereHas('tracks', fn (Builder $q) => $q->where('track_type', $track->track_type)->whereNotIn('created_by', [$track->created_by]))
                    ->orWhereDoesntHave('tracks');
            })
            ->first();

        if (! $battle) {
            $battle = Battle::forceCreate([
                'created_by' => auth()->id(),
                'battle_state' => BattleStates::OPEN,
                'battle_type' => BattleTypes::TRACK,
            ]);
        }

        $battle->tracks()->save($track);

        if ($battle->tracks()->count() === 2) {
            $this->changeStateToInProgress($battle);
        }

        return $battle->fresh();
    }

    public function createForDesign(Design $design): Battle
    {
        /** @var Battle $battle */
        $battle = Battle::where('battle_state', BattleStates::OPEN)
            ->where(function (Builder $builder) use ($design) {
                $builder->whereHas('designs', fn (Builder $q) => $q->where('design_type', $design->design_type->value)->whereNotIn('created_by', [$design->created_by]))
                    ->orWhereDoesntHave('designs');
            })
            ->first();

        if (! $battle) {
            $battle = Battle::forceCreate([
                'created_by' => auth()->id(),
                'battle_state' => BattleStates::OPEN,
                'battle_type' => BattleTypes::DESIGN,
            ]);
        }

        $battle->designs()->save($design);

        if ($battle->designs()->count() === 2) {
            $this->changeStateToInProgress($battle);
        }

        return $battle->fresh();
    }

    public function setWinner(Battle $battle): ?Battle
    {
        $creation = $battle->getWinningCreation();

        $battle
            ->forceFill(['won_by' => $creation?->created_by])
            ->winner()
            ->associate($creation)
            ->save();

        return $battle;
    }

    private function changeStateToInProgress(Battle $battle): void
    {
        try {
            $battle->fresh()->changeState(BattleStates::IN_PROGRESS); // TODO: remove fresh() when StateMachines supports enums
        } catch (Exception $e) {
            Logger::exception($e);

            return;
        }
    }
}
