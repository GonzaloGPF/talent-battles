<?php

namespace Tests\Unit\Jobs;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Jobs\CheckBattlesToFinish;
use App\Models\Battle;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckBattlesToFinishTest extends TestCase
{
    use RefreshDatabase;

    private CheckBattlesToFinish $job;

    private Battle $openBattle;

    private Battle $finishedBattle;

    private Battle $inProgressBattleOld;

    private Battle $inProgressBattleRecent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->battleTTL = config('company.battle_ttl');
        $this->job = resolve(CheckBattlesToFinish::class);
        $this->openBattle = create(Battle::class, ['battle_state' => BattleStates::OPEN->value]); // should be ignored
        $this->finishedBattle = create(Battle::class, ['battle_state' => BattleStates::FINISHED->value]); // should be ignored
        $this->inProgressBattleOld = create(Battle::class, [
            'battle_state' => BattleStates::IN_PROGRESS->value,
            'battle_type' => BattleTypes::TRACK,
            'created_at' => today()->subDays($this->battleTTL),
        ]);
        $this->inProgressBattleRecent = create(Battle::class, [ // should be ignored
            'battle_state' => BattleStates::IN_PROGRESS->value,
            'battle_type' => BattleTypes::TRACK,
            'created_at' => today()->subDays(2),
        ]);
    }

    #[Test]
    public function it_checks_battles_to_finish_correctly()
    {
        $this->addTracks($this->inProgressBattleOld);
        $this->addTracks($this->inProgressBattleRecent);

        $this->job->handle();

        $this->assertEquals(BattleStates::OPEN->value, $this->openBattle->fresh()->battle_state);
        $this->assertEquals(BattleStates::FINISHED->value, $this->finishedBattle->fresh()->battle_state);
        $this->assertEquals(BattleStates::FINISHED->value, $this->inProgressBattleOld->fresh()->battle_state);
        $this->assertEquals(BattleStates::IN_PROGRESS->value, $this->inProgressBattleRecent->fresh()->battle_state);
    }

    #[Test]
    public function a_battle_that_cannot_finish_does_not_stop_the_others()
    {
        $battleWithoutOpponent = $this->createExpiredBattle(daysAgo: $this->battleTTL + 2);
        $battleWithOpponent = $this->createExpiredBattle(daysAgo: $this->battleTTL + 1);
        $this->addTracks($battleWithOpponent);

        $this->job->handle();

        $this->assertEquals(BattleStates::IN_PROGRESS->value, $battleWithoutOpponent->fresh()->battle_state);
        $this->assertEquals(BattleStates::FINISHED->value, $battleWithOpponent->fresh()->battle_state);
    }

    #[Test]
    public function it_finishes_one_batch_of_battles_per_run()
    {
        $tracks = create(Track::class, [], 2);
        $battles = collect(range(0, CheckBattlesToFinish::BATCH_SIZE))
            ->map(fn () => $this->createExpiredBattle(daysAgo: $this->battleTTL + 1))
            ->each(fn (Battle $battle) => $battle->tracks()->saveMany($tracks));

        $this->job->handle();

        $finished = Battle::whereKey($battles->pluck('id'))
            ->where('battle_state', BattleStates::FINISHED->value)
            ->count();

        $this->assertEquals(CheckBattlesToFinish::BATCH_SIZE, $finished);
    }

    #[Test]
    public function it_does_nothing_while_another_run_is_finishing_battles()
    {
        $this->addTracks($this->inProgressBattleOld);
        Cache::lock(CheckBattlesToFinish::class, 300)->get();

        $this->job->handle();

        $this->assertEquals(BattleStates::IN_PROGRESS->value, $this->inProgressBattleOld->fresh()->battle_state);
    }

    private function createExpiredBattle(int $daysAgo): Battle
    {
        return create(Battle::class, [
            'battle_state' => BattleStates::IN_PROGRESS->value,
            'battle_type' => BattleTypes::TRACK,
            'created_at' => today()->subDays($daysAgo),
        ]);
    }

    private function addTracks(Battle $battle): void
    {
        /** @var Track $track1 */
        $track1 = create(Track::class);
        /** @var Track $track2 */
        $track2 = create(Track::class);

        $battle->tracks()->saveMany([$track1, $track2]);
    }
}
