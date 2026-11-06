<?php

namespace App\Listeners;

use App\Events\BattleFinished;
use App\Services\BattleService;

class SetBattleWinner
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private readonly BattleService $battleService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BattleFinished $event): void
    {
        $this->battleService->setWinner($event->battle);
    }
}
