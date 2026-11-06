<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Like;
use App\Notifications\LikeReceived;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LikeCreated implements NotyEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Like $like)
    {
        //
    }

    public function notify(): void
    {
        $targetUser = $this->like->getTargetUser();

        $targetUser?->notify(new LikeReceived($this->like));
    }
}
