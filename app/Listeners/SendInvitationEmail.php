<?php

namespace App\Listeners;

use App\Events\InvitationCreated;
use App\Services\EmailService;

class SendInvitationEmail
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
    public function handle(InvitationCreated $event): void
    {
        $this->emailService->createInvitationEmail($event->invitation);
    }
}
