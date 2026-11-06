<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\EmailService;

class SendVerificationEmail
{
    /**
     * Create the event listener.
     */
    public function __construct(private readonly EmailService $emailService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $this->emailService->createVerifyEmail($event->user);
    }
}
