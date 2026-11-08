<?php

namespace App\Services;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Facades\Logger;
use App\Helpers\NotificationHelper;
use App\Jobs\CheckBattlesToFinish;
use App\Jobs\MatchBattles;
use App\Models\Activity;
use App\Models\Battle;
use App\Models\Comment;
use App\Models\Design;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Notification;
use App\Models\Track;
use App\Models\User;
use App\Notifications\BattleDraw;
use App\Notifications\BattleLost;
use App\Notifications\BattleStarted;
use App\Notifications\BattleWon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BattleService
{
    /**
     * Minutes between two refreshes of the battles.
     */
    private const REFRESH_MINUTES = 10;

    public function __construct(
        private readonly PointsService $pointsService,
        private readonly UserService $userService,
    ) {}

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

    /**
     * Deletes the battles with everything that points at them, and takes back what finishing them gave. Their votes
     * and their places in the creations go with them, by cascade.
     *
     * @param  EloquentCollection<int, Battle>  $battles
     */
    public function deleteBattles(EloquentCollection $battles): void
    {
        $battles->load(['winner.createdBy', 'votes.createdBy', 'tracks.createdBy', 'designs.createdBy']);

        $ids = $battles->modelKeys();
        $morphClass = (new Battle)->getMorphClass();
        $participants = $battles->flatMap(fn (Battle $battle) => $battle->getParticipants())->unique('id');

        DB::transaction(function () use ($battles, $ids, $morphClass, $participants) {
            $battles->where('battle_state', BattleStates::FINISHED->value)
                ->each(fn (Battle $battle) => $this->pointsService->takeBackBattlePoints($battle));

            Comment::where('commentable_type', $morphClass)->whereIn('commentable_id', $ids)->delete();
            Like::where('likeable_type', $morphClass)->whereIn('likeable_id', $ids)->delete();
            Favorite::where('favoritable_type', $morphClass)->whereIn('favoritable_id', $ids)->delete();
            Activity::where('target_type', $morphClass)->whereIn('target_id', $ids)->delete();
            Notification::withoutGlobalScopes()->whereIn('data->noty_id', $this->notificationIds($ids))->delete();
            Battle::whereKey($ids)->delete();

            $participants->each(fn (User $user) => $this->userService->updateRating($user));
        });
    }

    /**
     * Ids that the notifications about these battles carry in their data.
     *
     * @param  array<int, int>  $battleIds
     * @return array<int, string>
     */
    private function notificationIds(array $battleIds): array
    {
        return collect($battleIds)
            ->crossJoin([BattleStarted::class, BattleWon::class, BattleLost::class, BattleDraw::class])
            ->map(fn (array $pair) => NotificationHelper::notyId(...$pair))
            ->all();
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
