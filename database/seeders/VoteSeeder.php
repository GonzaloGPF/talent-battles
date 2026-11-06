<?php

namespace Database\Seeders;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\User;
use App\Services\BattleService;
use App\Services\VoteService;
use Illuminate\Database\Seeder;

class VoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $voteService = resolve(VoteService::class);
        $battleService = resolve(BattleService::class);

        $users = User::all();
        $battles = Battle::with(['tracks', 'designs'])
            ->where('battle_state', BattleStates::FINISHED)
            ->get();

        $battles
            ->where('battle_type', BattleTypes::TRACK)
            ->each(function (Battle $battle) use ($users, $voteService) {
                collect(range(0, rand(1, 10)))
                    ->each(fn () => $voteService->create(
                        $battle->id,
                        $battle->tracks->random(),
                        $users->random()
                    ));
            });

        $battles
            ->where('battle_type', BattleTypes::DESIGN)
            ->each(function (Battle $battle) use ($users, $voteService) {
                collect(range(0, rand(1, 10)))
                    ->each(fn () => $voteService->create(
                        $battle->id,
                        $battle->designs->random(),
                        $users->random()
                    ));
            });

        $battles->each(fn (Battle $battle) => $battleService->setWinner($battle));
    }
}
