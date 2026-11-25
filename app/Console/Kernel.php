<?php

namespace App\Console;

use App\Jobs\CheckBattlesToFinish;
use App\Jobs\MatchBattles;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Production runs no cron: these jobs also run after the battles are listed. The schedule keeps them going wherever
     * a scheduler runs.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new CheckBattlesToFinish)->hourly();
        $schedule->job(new MatchBattles)->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
