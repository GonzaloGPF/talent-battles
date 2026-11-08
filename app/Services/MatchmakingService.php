<?php

namespace App\Services;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\Roles;
use App\Facades\Logger;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Talent Battles keeps battles in progress for the community to vote on. It only pairs creations whose owners accept
 * challenges, and it creates the battles with its own account, never on behalf of a participant.
 */
class MatchmakingService
{
    /**
     * Creations that can battle: the attribute two opponents must share, their relation and their type of battle.
     *
     * @var array<class-string<Track|Design>, array{type: string, relation: string, battle: BattleTypes}>
     */
    private const CREATIONS = [
        Track::class => ['type' => 'track_type', 'relation' => 'tracks', 'battle' => BattleTypes::TRACK],
        Design::class => ['type' => 'design_type', 'relation' => 'designs', 'battle' => BattleTypes::DESIGN],
    ];

    /**
     * Creates battles until as many are in progress as the configuration asks for, and returns how many it created.
     * The lock stops two runs from putting the same creation in two battles.
     */
    public function fillBattlesInProgress(): int
    {
        return Cache::lock(self::class, 300)->get(function () {
            $missing = config('company.battles_in_progress') - Battle::areInProgress()->count();

            if ($missing <= 0) {
                return 0;
            }

            $platformUserId = User::where('role', Roles::ADMIN)->oldest('id')->value('id');
            $candidates = $this->candidates();
            $created = 0;

            while ($created < $missing && $candidates->isNotEmpty()) {
                $creation = $candidates->shift();
                $opponent = $this->findOpponent($creation, $candidates);

                if (! $opponent) {
                    continue;
                }

                $candidates = $candidates->reject(fn (Track|Design $candidate) => $candidate->is($opponent))->values();

                if ($this->createBattle($creation, $opponent, $platformUserId)) {
                    $created++;
                }
            }

            return $created;
        }) ?: 0;
    }

    /**
     * Creations that accept challenges and are not in a battle, those with fewer battles first, so every one gets
     * its turn. The order between creations with the same number of battles is random.
     *
     * @return Collection<int, Track|Design>
     */
    private function candidates(): Collection
    {
        return collect(self::CREATIONS)
            ->flatMap(fn (array $settings, string $model) => $model::query()
                ->select(['id', 'created_by', $settings['type']])
                ->where('can_challenge', true)
                ->whereHas('createdBy')
                ->whereDoesntHave('battles', fn (Builder $query) => $query->whereIn('battle_state', [BattleStates::OPEN, BattleStates::IN_PROGRESS]))
                ->withCount('battles')
                ->get())
            ->shuffle()
            ->sortBy('battles_count')
            ->values();
    }

    /**
     * The first candidate of the same kind, from another owner, that has never faced the creation.
     *
     * @param  Collection<int, Track|Design>  $candidates
     */
    private function findOpponent(Track|Design $creation, Collection $candidates): Track|Design|null
    {
        $type = self::CREATIONS[$creation::class]['type'];
        $previousOpponentIds = $this->previousOpponentIds($creation);

        return $candidates->first(fn (Track|Design $candidate) => $candidate instanceof $creation
            && $candidate->{$type} === $creation->{$type}
            && $candidate->created_by !== $creation->created_by
            && ! $previousOpponentIds->contains($candidate->id));
    }

    /**
     * Ids of the creations that have shared a battle with the creation, its own included.
     *
     * @return Collection<int, int>
     */
    private function previousOpponentIds(Track|Design $creation): Collection
    {
        $relation = self::CREATIONS[$creation::class]['relation'];

        return $creation::query()
            ->whereHas('battles', fn (Builder $battles) => $battles->whereHas($relation, fn (Builder $creations) => $creations->whereKey($creation->id)))
            ->pluck('id');
    }

    /**
     * The battle starts through the state machine, like any other, so its participants are notified.
     */
    private function createBattle(Track|Design $creation, Track|Design $opponent, ?int $platformUserId): bool
    {
        $settings = self::CREATIONS[$creation::class];

        try {
            DB::transaction(function () use ($creation, $opponent, $platformUserId, $settings) {
                $battle = Battle::forceCreate([
                    'created_by' => $platformUserId,
                    'battle_state' => BattleStates::OPEN->value,
                    'battle_type' => $settings['battle'],
                ]);

                $battle->{$settings['relation']}()->attach([$creation->id, $opponent->id]);

                $battle->changeState(BattleStates::IN_PROGRESS);
            });

            return true;
        } catch (Exception $exception) {
            Logger::exception($exception);

            return false;
        }
    }
}
