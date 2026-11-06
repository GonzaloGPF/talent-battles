<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Favorite;
use App\Notifications\FavoriteReceived;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FavoriteCreated implements NotyEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Favorite $favorite)
    {
        //
    }

    public function notify(): void
    {
        $targetUser = $this->favorite->getTargetUser();

        $targetUser?->notify(new FavoriteReceived($this->favorite));
    }
}
