<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Comment;
use App\Notifications\CommentReceived;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements NotyEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Comment $comment)
    {
        //
    }

    public function notify(): void
    {
        $targetUser = $this->comment->getTargetUser();

        $targetUser?->notify(new CommentReceived($this->comment));
    }
}
