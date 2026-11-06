<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Download;
use App\Notifications\DownloadReceived;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DownloadCreated implements NotyEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Download $download)
    {
        //
    }

    public function notify(): void
    {
        $targetUser = $this->download->getTargetUser();

        $targetUser?->notify(new DownloadReceived($this->download));
    }
}
