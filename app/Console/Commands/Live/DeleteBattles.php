<?php

namespace App\Console\Commands\Live;

use App\Enums\BattleStates;
use App\Models\Battle;
use App\Services\BattleService;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Carbon;

class DeleteBattles extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-battles
                            {before : The battles created before this date are deleted}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the battles created before a date, and take back what finishing them gave';

    /**
     * Execute the console command.
     */
    public function handle(BattleService $battleService): int
    {
        $before = Carbon::parse($this->argument('before'));
        $battles = Battle::where('created_at', '<', $before)->get();
        $finished = $battles->where('battle_state', BattleStates::FINISHED->value)->count();

        $this->info("{$battles->count()} Battles were created before {$before->toDateTimeString()}, $finished of them finished.");

        if ($battles->isEmpty() || ! $this->confirmToProceed()) {
            return self::SUCCESS;
        }

        $battleService->deleteBattles($battles);

        $this->info("Deleted {$battles->count()} Battles!");

        return self::SUCCESS;
    }
}
