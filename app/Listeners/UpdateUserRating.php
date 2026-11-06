<?php

namespace App\Listeners;

use App\Events\BattleFinished;
use App\Models\User;
use App\Services\UserService;

class UpdateUserRating
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private readonly UserService $userService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BattleFinished $event): void
    {
        $event->battle
            ->getParticipants()
            ->each(fn (User $user) => $this->userService->updateRating($user));
    }
}
