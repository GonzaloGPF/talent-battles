<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Battle;
use App\Models\User;
use App\Notifications\BattleStarted;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BattleInProgress implements NotyEventInterface, ShouldBroadcast
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
        $this->battle
            ->getParticipants()
//            ->filter(fn (User $user) => $user->id !== auth()->id())
            ->each(fn (User $user) => $user->notify(new BattleStarted($this->battle)));
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
