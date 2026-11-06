<?php

namespace Tests\Feature\Filters;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BattleFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        create(Battle::class, [], 3);
    }

    #[Test]
    public function it_can_filter_battles_by_battle_state()
    {
        Battle::query()->update(['battle_state' => BattleStates::OPEN->value]);

        create(Battle::class, ['battle_state' => BattleStates::FINISHED->value]);

        $battles = Battle::filter(['battle_states' => [BattleStates::FINISHED->value]])->get();

        $this->assertCount(1, $battles);
    }

    #[Test]
    public function it_can_filter_battles_by_battle_type()
    {
        Battle::query()->update(['battle_type' => BattleTypes::TRACK]);

        create(Battle::class, ['battle_type' => BattleTypes::DESIGN]);

        $battles = Battle::filter(['battle_types' => [BattleTypes::DESIGN]])->get();

        $this->assertCount(1, $battles);
    }

    #[Test]
    public function it_can_filter_battles_by_in_progress()
    {
        Battle::query()->update(['battle_state' => BattleStates::OPEN->value]);

        create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS->value]);

        $battles = Battle::filter(['in_progress' => true])->get();

        $this->assertCount(1, $battles);
    }

    #[Test]
    public function it_can_filter_battles_by_is_finished()
    {
        Battle::query()->update(['battle_state' => BattleStates::OPEN->value]);

        create(Battle::class, ['battle_state' => BattleStates::FINISHED->value]);

        $battles = Battle::filter(['is_finished' => true])->get();

        $this->assertCount(1, $battles);
    }

    #[Test]
    public function it_can_filter_battles_by_is_next()
    {
        /** @var User $user */
        $user = create(User::class);
        $this->signIn($user);

        /** @var Battle $battle */
        $battle = create(Battle::class, [  // should be considered
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ]);
        $battle->tracks()->saveMany([
            create(Track::class, ['created_by' => create(User::class)->id]),
            create(Track::class, ['created_by' => create(User::class)->id]),
        ]);

        /** @var Battle $battle */
        $battle = create(Battle::class, [  // should be ignored because wrong state
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::OPEN->value,
        ]);
        $battle->tracks()->saveMany([
            create(Track::class, ['created_by' => create(User::class)->id]),
            create(Track::class, ['created_by' => create(User::class)->id]),
        ]);

        /** @var Battle $battle */
        $battle = create(Battle::class, [  // should be ignored because wrong state
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::FINISHED->value,
        ]);
        $battle->tracks()->saveMany([
            create(Track::class, ['created_by' => create(User::class)->id]),
            create(Track::class, ['created_by' => create(User::class)->id]),
        ]);

        /** @var Battle $battle */
        $battle = create(Battle::class, [  // should be ignored because already voted
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ]);
        $battle->tracks()->saveMany([
            create(Track::class, ['created_by' => create(User::class)->id]),
            create(Track::class, ['created_by' => create(User::class)->id]),
        ]);
        $battle->votes()->save(make(Vote::class, [
            'created_by' => $user->id,
            'votable_id' => 1,
            'votable_type' => Track::class,
        ]));

        /** @var Battle $battle */
        $battle = create(Battle::class, [  // should be ignored because not enough tracks
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ]);
        $battle->tracks()->saveMany([
            create(Track::class, ['created_by' => create(User::class)->id]),
        ]);

        /** @var Battle $battle */
        $battle = create(Battle::class, [  // should be ignored because track its own
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ]);
        $battle->tracks()->saveMany([
            create(Track::class, ['created_by' => create(User::class)->id]),
            create(Track::class, ['created_by' => $user->id]),
        ]);

        $battles = Battle::filter(['is_next' => true])->get();

        $this->assertCount(1, $battles);
    }

    #[Test]
    public function it_can_filter_battles_by_name_of_tracks()
    {
        /** @var Track $track */
        $track = create(Track::class, ['name' => 'Test']);
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_type' => BattleTypes::TRACK]);
        $battle->tracks()->save($track);

        $battles = Battle::filter(['name' => 'Test'])->get();

        $this->assertCount(1, $battles);
    }

    #[Test]
    public function it_can_filter_battles_by_won()
    {
        $this->signIn();

        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => auth()->id(),
        ]);
        create(Battle::class, [
            'battle_state' => BattleStates::FINISHED->value,
            'winner_type' => Track::class,
            'winner_id' => $track->id,
        ]);

        $battles = Battle::filter(['won' => true])->get();

        $this->assertCount(1, $battles);
    }

    #[Test]
    public function it_can_filter_battles_by_participants()
    {
        $user1 = create(User::class);
        $track1 = create(Track::class, ['created_by' => $user1->id]);
        $battle1 = create(Battle::class, ['battle_type' => BattleTypes::TRACK]);
        $battle1->tracks()->save($track1);

        $battles = Battle::filter(['by_participants' => [$user1->id]])->get();

        $this->assertCount(1, $battles);
    }

    #[Test]
    public function it_can_filter_battles_by_won_by()
    {
        /** @var User $winnerUser */
        $winnerUser = create(User::class);

        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => $winnerUser->id,
        ]);
        create(Battle::class, [
            'battle_state' => BattleStates::FINISHED->value,
            'winner_type' => Track::class,
            'winner_id' => $track->id,
        ]);

        $battles = Battle::filter(['won_by' => [$winnerUser->id]])->get();

        $this->assertCount(1, $battles);
    }

    #[Test]
    public function it_can_filter_battles_by_lost_by()
    {
        /** @var User $winnerUser */
        $winnerUser = create(User::class);

        /** @var Track $winningTrack */
        $winningTrack = create(Track::class, [
            'created_by' => $winnerUser->id,
        ]);

        /** @var User $loserUser */
        $loserUser = create(User::class);

        /** @var Track $losingTrack */
        $losingTrack = create(Track::class, [
            'created_by' => $loserUser->id,
        ]);

        /** @var Battle $battle */
        $battle = create(Battle::class, [
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::FINISHED->value,
            'winner_type' => Track::class,
            'winner_id' => $winningTrack->id,
        ]);

        $battle->tracks()->saveMany([$winningTrack, $losingTrack]);

        $battles = Battle::filter(['lost_by' => [$winnerUser->id]])->get();

        $this->assertCount(0, $battles);

        $battles = Battle::filter(['lost_by' => [$loserUser->id]])->get();

        $this->assertCount(1, $battles);
    }
}
