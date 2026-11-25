<?php

namespace Tests\Feature\Battles;

use App\Jobs\CheckBattlesToFinish;
use App\Jobs\MatchBattles;
use App\Models\Battle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewBattleTest extends TestCase
{
    use RefreshDatabase;

    private Collection $battles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->battles = create(Battle::class, [], 3);
    }

    #[Test]
    public function users_can_fetch_battles()
    {
        $data = $this->getJson(route('battles.index'))
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(3, $data);
    }

    #[Test]
    public function users_can_fetch_a_specific_battle()
    {
        $battle = $this->battles->first();

        $data = $this->getJson(route('battles.show', $battle))
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($battle->id, $data['id']);
    }

    #[Test]
    public function a_battle_says_which_model_it_is_so_it_can_be_commented_and_shared()
    {
        $this->getJson(route('battles.show', $this->battles->first()))
            ->assertJsonPath('data.model', 'battle');
    }

    #[Test]
    public function fetching_battles_refreshes_them_after_the_response()
    {
        Bus::fake([CheckBattlesToFinish::class, MatchBattles::class]);

        $this->getJson(route('battles.index'))
            ->assertSuccessful();

        Bus::assertDispatchedAfterResponse(CheckBattlesToFinish::class);
        Bus::assertDispatchedAfterResponse(MatchBattles::class);
    }

    #[Test]
    public function fetching_a_battle_refreshes_them_after_the_response()
    {
        Bus::fake([CheckBattlesToFinish::class, MatchBattles::class]);

        $this->getJson(route('battles.show', $this->battles->first()))
            ->assertSuccessful();

        Bus::assertDispatchedAfterResponse(CheckBattlesToFinish::class);
        Bus::assertDispatchedAfterResponse(MatchBattles::class);
    }

    #[Test]
    public function battles_are_refreshed_at_most_once_every_ten_minutes()
    {
        Bus::fake([CheckBattlesToFinish::class, MatchBattles::class]);

        $this->getJson(route('battles.index'));
        $this->travel(9)->minutes();
        $this->getJson(route('battles.index'));

        Bus::assertDispatchedAfterResponseTimes(CheckBattlesToFinish::class, 1);
        Bus::assertDispatchedAfterResponseTimes(MatchBattles::class, 1);

        $this->travel(2)->minutes();
        $this->getJson(route('battles.index'));

        Bus::assertDispatchedAfterResponseTimes(CheckBattlesToFinish::class, 2);
        Bus::assertDispatchedAfterResponseTimes(MatchBattles::class, 2);
    }
}
