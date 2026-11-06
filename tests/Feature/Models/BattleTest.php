<?php

namespace Tests\Feature\Models;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;
use App\Traits\HasCreatedBy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BattleTest extends TestCase
{
    use RefreshDatabase;

    private Battle $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = create(Battle::class, ['battle_type' => BattleTypes::TRACK]);
    }

    #[Test]
    public function battle_can_query_by_are_open_scope()
    {
        $this->model
            ->forceFill(['battle_state' => BattleStates::OPEN])
            ->saveQuietly();

        //        create(Battle::class, ['battle_state' => BattleStates::OPEN]);
        create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS]);
        create(Battle::class, ['battle_state' => BattleStates::FINISHED]);

        $this->assertEquals(1, Battle::areOpen()->count());
    }

    #[Test]
    public function battle_can_query_by_participants()
    {
        $user1 = create(User::class);
        $track1 = create(Track::class, ['created_by' => $user1->id]);
        $battle1 = create(Battle::class, ['battle_type' => BattleTypes::TRACK]);
        $battle1->tracks()->save($track1);

        $user2 = create(User::class);
        $track2 = create(Track::class, ['created_by' => $user2->id]);
        $battle2 = create(Battle::class, ['battle_type' => BattleTypes::TRACK]);
        $battle2->tracks()->save($track2);

        $battles = Battle::byParticipants([$user1->id])->get();

        $this->assertEquals(1, $battles->count());
        $this->assertEquals($battle1->id, $battles[0]['id']);
    }

    #[Test]
    public function battle_user_has_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }

    #[Test]
    public function battle_belongs_to_many_tracks()
    {
        $this->assertBelongsToMany($this->model, Track::class);
    }

    #[Test]
    public function battle_morphs_to_winner()
    {
        $this->assertMorphsTo($this->model, 'winner');
    }

    #[Test]
    public function battle_has_many_points()
    {
        $this->assertHasMany($this->model, Vote::class);
    }

    #[Test]
    public function battle_knows_what_is_the_winning_track()
    {
        /** @var User $userA */
        $userA = create(User::class);
        /** @var Track $trackA */
        $trackA = create(Track::class, ['created_by' => $userA->id]);
        create(Vote::class, [
            'created_by' => create(User::class)->id,
            'votable_id' => $trackA->id,
            'votable_type' => Track::class,
            'value' => 5,
        ]);
        create(Vote::class, [
            'created_by' => create(User::class)->id,
            'votable_id' => $trackA->id,
            'votable_type' => Track::class,
            'value' => 5,
        ]);

        /** @var User $userB */
        $userB = create(User::class);
        /** @var Track $trackB */
        $trackB = create(Track::class, ['created_by' => $userB->id]);
        create(Vote::class, [
            'created_by' => create(User::class)->id,
            'votable_id' => $trackB->id,
            'votable_type' => Track::class,
            'value' => 1,
        ]);
        create(Vote::class, [
            'created_by' => create(User::class)->id,
            'votable_id' => $trackB->id,
            'votable_type' => Track::class,
            'value' => 2,
        ]);

        $this->model->tracks()->saveMany([$trackA, $trackB]);

        $track = $this->model->getWinningCreation();

        $this->assertEquals($trackA->id, $track->id);
    }

    #[Test]
    public function battle_knows_if_its_a_draw()
    {
        /** @var User $userA */
        $userA = create(User::class);
        /** @var Track $trackA */
        $trackA = create(Track::class, ['created_by' => $userA->id]);
        create(Vote::class, [
            'created_by' => create(User::class)->id,
            'votable_id' => $trackA->id,
            'votable_type' => Track::class,
            'value' => 5,
        ]);
        create(Vote::class, [
            'created_by' => create(User::class)->id,
            'votable_id' => $trackA->id,
            'votable_type' => Track::class,
            'value' => 5,
        ]);

        /** @var User $userB */
        $userB = create(User::class);
        /** @var Track $trackB */
        $trackB = create(Track::class, ['created_by' => $userB->id]);
        create(Vote::class, [
            'created_by' => create(User::class)->id,
            'votable_id' => $trackB->id,
            'votable_type' => Track::class,
            'value' => 5,
        ]);
        create(Vote::class, [
            'created_by' => create(User::class)->id,
            'votable_id' => $trackB->id,
            'votable_type' => Track::class,
            'value' => 5,
        ]);

        $this->model->tracks()->saveMany([$trackA, $trackB]);

        $this->assertTrue($this->model->isDraw());
    }

    #[Test]
    public function battle_has_many_votes()
    {
        $this->assertHasMany($this->model, Vote::class);
    }

    #[Test]
    public function battle_belongs_to_a_won_by()
    {
        $this->assertBelongsTo($this->model, User::class, 'wonBy');
    }
}
