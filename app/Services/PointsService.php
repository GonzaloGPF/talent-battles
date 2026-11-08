<?php

namespace App\Services;

use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;

class PointsService
{
    private float $drawPoints;

    private float $winPoints;

    private float $winnerVotePoints;

    public function __construct()
    {
        $this->drawPoints = (float) config('company.points.awards.draw_battle');
        $this->winPoints = (float) config('company.points.awards.win_battle');
        $this->winnerVotePoints = (float) config('company.points.awards.do_winner_vote');
    }

    public function earnPoints(float $points, ?User $user = null, bool $withoutXp = false): User
    {
        if (! $points) { // if not points or if its zero
            return $user;
        }

        $user = $user ?? auth()->user();

        $user->points += $points;

        if ($withoutXp) {
            $user->save();

            return $user;
        }

        $user->experience += $points;

        $user->save();

        return $user;
    }

    public function spendPoints(float $points, ?User $user = null): User
    {
        $user = $user ?? auth()->user();

        $user->points -= $points;

        if ($user->points < 0) {
            $user->points = 0;
        }

        $user->experience += $points / 2;

        $user->save();

        return $user;
    }

    public function giveDrawPoints(Battle $battle): void
    {
        $battle
            ->getParticipants()
            ->each(fn (User $user) => $this->earnPoints($this->drawPoints, $user));
    }

    public function givePointsToWinner(Battle $battle): void
    {
        /** @var Track|Design|null $winnerCreation */
        $winnerCreation = $battle->winner;

        if (! in_array(get_class($winnerCreation), [Track::class, Design::class])) {
            return;
        }

        $winnerUser = $winnerCreation?->createdBy;

        if (! $winnerUser) {
            return;
        }

        $this->earnPoints($this->winPoints, $winnerUser);
    }

    /**
     * Takes back what finishing the battle gave: the points of the draw or of the winner, and those of its voters.
     */
    public function takeBackBattlePoints(Battle $battle): void
    {
        /** @var Track|Design|null $winnerCreation */
        $winnerCreation = $battle->winner;

        if (! $winnerCreation) {
            $battle->getParticipants()
                ->each(fn (User $user) => $this->earnPoints(-$this->drawPoints, $user));

            return;
        }

        if ($winnerCreation->createdBy) {
            $this->earnPoints(-$this->winPoints, $winnerCreation->createdBy);
        }

        $battle->votes
            ->filter(fn (Vote $vote) => $vote->votable_id === $winnerCreation->id)
            ->each(fn (Vote $vote) => $this->earnPoints(-$this->winnerVotePoints, $vote->createdBy));
    }

    public function givePointsToVoters(Battle $battle): void
    {
        $winner = $battle->winner;

        if (! $winner) {
            return;
        }

        $battle->votes
            ->filter(fn (Vote $vote) => $vote->votable_id === $winner->id)
            ->each(fn (Vote $vote) => $this->earnPoints($this->winnerVotePoints, $vote->createdBy));
    }
}
