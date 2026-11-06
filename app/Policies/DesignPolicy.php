<?php

namespace App\Policies;

use App\Enums\BattleStates;
use App\Models\Design;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\HandlesAuthorization;

class DesignPolicy
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
        if (! $user->verified_at && $user->designs()->count() >= 3) {
            throw new AuthorizationException(trans('exceptions.unverified_email'));
        }

        return true;
    }

    /**
     * @throws AuthorizationException
     */
    public function update(User $user, Design $design): bool
    {
        if ($design->battles()->where('battle_state', BattleStates::IN_PROGRESS)->exists()) {
            throw new AuthorizationException(trans('validation.in_battle'));
        }

        return $design->created_by === $user->id;
    }

    /**
     * @throws AuthorizationException
     */
    public function delete(User $user, Design $design): bool
    {
        if ($design->battles()->where('battle_state', BattleStates::IN_PROGRESS)->exists()) {
            throw new AuthorizationException(trans('validation.in_battle'));
        }

        return $design->created_by === $user->id;
    }

    //    /**
    //     * @param User $user
    //     * @param Design $design
    //     * @param Design $opponent
    //     * @return bool
    //     * @throws AuthorizationException
    //     */
    //    public function challenge(User $user, Design $design, Design $opponent): bool // TODO: test it
    //    {
    //        if ($user->id !== $design->created_by) {
    //            throw new AuthorizationException();
    //        }
    //
    //        if ($user->id === $opponent->created_by) {
    //            throw new AuthorizationException(trans('validation.cant_challenge_yourself'));
    //        }
    //
    //        if ($design->battles()->whereIn('battle_state', [BattleStates::OPEN, BattleStates::IN_PROGRESS])->exists()) {
    //            throw new AuthorizationException(trans('validation.already_in_battle'));
    //        }
    //
    //        return Gate::allows('create', Battle::class);
    //    }
}
