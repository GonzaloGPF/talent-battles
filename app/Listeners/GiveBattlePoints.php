<?php

namespace App\Listeners;

use App\Events\BattleFinished;
use App\Services\PointsService;

class GiveBattlePoints
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private readonly PointsService $pointsService) {}

    /**
     * Handle the event.
     */
    public function handle(BattleFinished $event): void
    {
        $winner = $event->battle->winner;
        $battle = $event->battle;

        if (! $winner) {
            $this->pointsService->giveDrawPoints($battle);

            return;
        }

        $this->pointsService->givePointsToWinner($battle);
        $this->pointsService->givePointsToVoters($battle);
    }
}
