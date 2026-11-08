<?php

namespace App\Jobs;

use App\Services\MatchmakingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class MatchBattles implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(MatchmakingService $matchmakingService): void
    {
        $matchmakingService->fillBattlesInProgress();
    }
}
