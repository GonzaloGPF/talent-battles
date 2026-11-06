<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\InvitationService;

class AssociateInvitation
{
    /**
     * Create the event listener.
     */
    public function __construct(private readonly InvitationService $invitationService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $this->invitationService->associateToUser($event->user);
    }
}
