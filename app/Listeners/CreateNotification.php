<?php

namespace App\Listeners;

use App\Interfaces\NotyEventInterface;

class CreateNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NotyEventInterface $event): void
    {
        $event->notify();
    }
}
