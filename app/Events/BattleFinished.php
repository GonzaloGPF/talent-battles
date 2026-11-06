<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Battle;
use App\Models\User;
use App\Notifications\BattleDraw;
use App\Notifications\BattleLost;
use App\Notifications\BattleWon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BattleFinished implements NotyEventInterface, ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Battle $battle)
    {
        //
    }

    public function notify(): void
    {
        /** @var User $winnerUser */
        $winnerUser = $this->battle->winner?->createdBy;
        $participants = $this->battle
            ->getParticipants();

        if ($winnerUser) {
            $winnerUser->notify(new BattleWon($this->battle, $winnerUser));

            $participants
                ->filter(fn (User $user) => $user->isNot($winnerUser))
                ->each(fn (User $user) => $user->notify(new BattleLost($this->battle, $user)));
        } else {
            $participants
                ->each(fn (User $user) => $user->notify(new BattleDraw($this->battle, $user)));
        }
    }

    /**
     * @return array<PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return $this->battle->getParticipants()
            ->map(fn (User $user) => new PrivateChannel($user->getChannelName()))
            ->toArray();
    }
}
