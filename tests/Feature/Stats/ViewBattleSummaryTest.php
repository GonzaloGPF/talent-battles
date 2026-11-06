<?php

namespace Tests\Feature\Stats;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewBattleSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_let_view_battle_summary_stats_data()
    {
        /** @var User $owner */
        $owner = create(User::class);
        $track = create(Track::class, ['created_by' => $owner->id]);
        /** @var Battle $wonBattle */
        $wonBattle = create(Battle::class, [
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::FINISHED->value,
        ]);

        $wonBattle->tracks()->save($track);
        $wonBattle->winner()->associate($track)->save();

        /** @var User $otherOwner */
        $otherOwner = create(User::class);
        $otherTrack = create(Track::class, ['created_by' => $otherOwner->id]);
        /** @var Battle $lostBattle */
        $lostBattle = create(Battle::class, [
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::FINISHED->value,
        ]);

        $lostBattle->tracks()->saveMany([$track, $otherTrack]);
        $lostBattle->winner()->associate($otherTrack)->save();

        $this->signIn($owner);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals(modelTitle('battle', true), $data['datasets'][0]['label']);
        $this->assertEquals([1, 1], $data['datasets'][0]['data']);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('stats.battles.summary'));
    }
}
