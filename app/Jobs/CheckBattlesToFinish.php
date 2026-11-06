<?php

namespace App\Jobs;

use App\Enums\BattleStates;
use App\Facades\Logger;
use App\Models\Battle;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckBattlesToFinish implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private float $battleTTL;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->battleTTL = floatval(config('company.battle_ttl'));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Battle::areInProgress()
                ->where('created_at', '<=', today()->subDays($this->battleTTL))
                ->get()
                ->each(fn (Battle $battle) => $battle->changeState(BattleStates::FINISHED));
        } catch (Exception $exception) {
            Logger::exception($exception);
        }
    }
}
