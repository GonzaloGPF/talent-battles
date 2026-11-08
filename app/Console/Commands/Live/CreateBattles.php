<?php

namespace App\Console\Commands\Live;

use App\Services\MatchmakingService;
use Illuminate\Console\Command;

class CreateBattles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-battles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill the battles in progress with matchups made by Talent Battles';

    /**
     * Execute the console command.
     */
    public function handle(MatchmakingService $matchmakingService): void
    {
        $total = $matchmakingService->fillBattlesInProgress();

        $this->info("Created $total Battles!");
    }
}
