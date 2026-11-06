<?php

namespace App\Policies;

use App\Enums\BattleStates;
use App\Models\Track;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrackPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @throws AuthorizationException
     */
    public function create(User $user): bool
    {
        if (! $user->verified_at && $user->tracks()->count() >= 3) {
            throw new AuthorizationException(trans('exceptions.unverified_email'));
        }

        return true;
    }

    /**
     * @throws AuthorizationException
     */
    public function update(User $user, Track $track): bool
    {
        //        if ($track->battles()->where('battle_state', BattleStates::IN_PROGRESS)->exists()) {
        //            throw new AuthorizationException(trans('validation.in_battle'));
        //        }

        return $track->created_by === $user->id;
    }

    /**
     * @throws AuthorizationException
     */
    public function delete(User $user, Track $track): bool
    {
        if ($track->battles()->where('battle_state', BattleStates::IN_PROGRESS)->exists()) {
            throw new AuthorizationException(trans('validation.in_battle'));
        }

        return $track->created_by === $user->id;
    }

    //    /**
    //     * @param User $user
    //     * @param Track $track
    //     * @param Track $opponent
    //     * @return bool
    //     * @throws AuthorizationException
    //     */
    //    public function challenge(User $user, Track $track, Track $opponent): bool // TODO: test it
    //    {
    //        if ($user->id !== $track->created_by) {
    //            throw new AuthorizationException();
    //        }
    //
    //        if ($user->id === $opponent->created_by) {
    //            throw new AuthorizationException(trans('validation.cant_challenge_yourself'));
    //        }
    //
    //        if ($track->battles()->whereIn('battle_state', [BattleStates::OPEN, BattleStates::IN_PROGRESS])->exists()) {
    //            throw new AuthorizationException(trans('validation.already_in_battle'));
    //        }
    //
    //        return Gate::allows('create', Battle::class);
    //    }
}
