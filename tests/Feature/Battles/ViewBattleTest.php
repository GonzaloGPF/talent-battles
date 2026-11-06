<?php

namespace Tests\Feature\Battles;

use App\Models\Battle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
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

    //    /** @test */
    //    public function authenticated_user_can_fetch_random_in_progress_battle_of_other_users()
    //    {
    //        $otherUser1 = create(User::class);
    //        $otherUser2 = create(User::class);
    //
    //        $track1 = create(Track::class, ['created_by' => $otherUser1->id]);
    //        $track2 = create(Track::class, ['created_by' => $otherUser2->id]);
    //
    //        /** @var Battle $battle */
    //        $battle = create(Battle::class, ['battle_state' => BattleState::IN_PROGRESS]);
    //        $battle->tracks()->saveMany([$track1, $track2]);
    //
    //        $data = $this->getJson(route('battles.next'))
    //            ->assertSuccessful()
    //            ->json('data');
    //
    //        $this->assertEquals($battle->id, $data['id']);;
    //    }
    //
    //    /** @test */
    //    public function when_fetching_a_random_battle_it_wont_return_finished_battle()
    //    {
    //        $otherUser1 = create(User::class);
    //        $otherUser2 = create(User::class);
    //
    //        $track1 = create(Track::class, ['created_by' => $otherUser1->id]);
    //        $track2 = create(Track::class, ['created_by' => $otherUser2->id]);
    //
    //        /** @var Battle $battle */
    //        $battle = create(Battle::class, ['battle_state' => BattleState::FINISHED]);
    //        $battle->tracks()->saveMany([$track1, $track2]);
    //
    //        $data = $this->getJson(route('battles.next'))
    //            ->assertSuccessful()
    //            ->json('data');
    //
    //        $this->assertEmpty($data);;
    //    }
    //
    //    /** @test */
    //    public function when_fetching_a_random_battle_it_wont_return_open_battle()
    //    {
    //        $otherUser1 = create(User::class);
    //
    //        $track1 = create(Track::class, ['created_by' => $otherUser1->id]);
    //
    //        /** @var Battle $battle */
    //        $battle = create(Battle::class, ['battle_state' => BattleState::OPEN]);
    //        $battle->tracks()->saveMany([$track1]);
    //
    //        $data = $this->getJson(route('battles.next'))
    //            ->assertSuccessful()
    //            ->json('data');
    //
    //        $this->assertEmpty($data);;
    //    }
    //
    //    /** @test */
    //    public function when_fetching_a_random_battle_it_wont_return_a_battle_already_voted()
    //    {
    //        $otherUser1 = create(User::class);
    //        $otherUser2 = create(User::class);
    //
    //        $track1 = create(Track::class, ['created_by' => $otherUser1->id]);
    //        $track2 = create(Track::class, ['created_by' => $otherUser2->id]);
    //
    //        /** @var Battle $battle */
    //        $battle = create(Battle::class, ['battle_state' => BattleState::IN_PROGRESS]);
    //        $battle->tracks()->saveMany([$track1, $track2]);
    //
    //        $battle->points()->save(create(Point::class, [ // Already voted
    //            'created_by' => auth()->id(),
    //            'creation_id' => $track1->id,
    //        ]));
    //
    //        $data = $this->getJson(route('battles.next'))
    //            ->assertSuccessful()
    //            ->json('data');
    //
    //        $this->assertEmpty($data);
    //    }
    //
    //    /** @test */
    //    public function when_fetching_a_random_battle_it_wont_return_an_own_battle()
    //    {
    //        $track1 = create(Track::class, ['created_by' => auth()->id()]); // My track
    //        $track2 = create(Track::class, ['created_by' => create(User::class)->id]);
    //
    //        /** @var Battle $battle */
    //        $battle = create(Battle::class, ['battle_state' => BattleState::IN_PROGRESS]);
    //        $battle->tracks()->saveMany([$track1, $track2]);
    //
    //        $data = $this->getJson(route('battles.next'))
    //            ->assertSuccessful()
    //            ->json('data');
    //
    //        $this->assertEmpty($data);
    //    }
}
