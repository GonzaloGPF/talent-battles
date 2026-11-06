<?php

namespace App\Listeners;

use App\Events\OrderSuccess;
use App\Services\EmailService;

class SendOrderSuccessEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private readonly EmailService $emailService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderSuccess $event): void
    {
        $this->emailService->createOrderSuccess($event->order);
    }
}
