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
use Illuminate\Support\Facades\Cache;

class CheckBattlesToFinish implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Battles finished in one run. A long backlog is finished over several runs instead of one long request.
     */
    public const BATCH_SIZE = 50;

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
     * Execute the job. The lock stops two runs from finishing the same battle twice.
     */
    public function handle(): void
    {
        Cache::lock(self::class, 300)->get(function () {
            Battle::areInProgress()
                ->where('created_at', '<=', today()->subDays($this->battleTTL))
                ->orderBy('created_at')
                ->limit(self::BATCH_SIZE)
                ->get()
                ->each(fn (Battle $battle) => $this->finish($battle));
        });
    }

    /**
     * A battle that cannot finish, like one without two creations, must not stop the others.
     */
    private function finish(Battle $battle): void
    {
        try {
            $battle->changeState(BattleStates::FINISHED);
        } catch (Exception $exception) {
            Logger::exception($exception);
        }
    }
}
