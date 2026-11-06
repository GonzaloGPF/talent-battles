<?php

namespace Tests\Feature\Battles;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\TrackTypes;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateTrackBattleTest extends TestCase
{
    use RefreshDatabase;

    private Track $track;

    private Track $otherTrack;

    private User $user;

    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        User::flushEventListeners();
        $this->user = create(User::class, [
            'points' => config('company.points.prices.create_battle'),
            'experience' => 0,
        ]);
        $this->track = create(Track::class, [
            'created_by' => $this->user->id,
            'can_challenge' => true,
            'track_type' => TrackTypes::BEAT,
        ]);
        $this->otherUser = create(User::class);
        $this->otherTrack = create(Track::class, [
            'created_by' => $this->otherUser->id,
            'can_challenge' => true,
            'track_type' => TrackTypes::BEAT,
        ]);
        $this->signIn($this->user);
    }

    #[Test]
    public function users_can_create_battles_for_a_track()
    {
        $this->makeRequest()
            ->assertSuccessful();

        /** @var Battle $battle */
        $battle = Battle::first();
        $this->assertEquals(BattleStates::OPEN->value, $battle->battle_state);
        $this->assertEquals(BattleTypes::TRACK, $battle->battle_type);
        $this->assertCount(1, $battle->tracks);

        $this->assertTrue($this->track->battles->contains($battle->id));
    }

    #[Test]
    public function when_creating_a_battle_for_a_track_it_will_decrease_the_user_points_correctly()
    {
        $battlePoints = config('company.points.prices.create_battle');

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals(0, $this->user->points);
        $this->assertEquals($battlePoints / 2, $this->user->experience);
    }

    #[Test]
    public function when_creating_a_battle_for_a_track_it_can_find_a_battle_already_created()
    {
        /** @var Track $track */
        $track = create(Track::class, [
            'track_type' => $this->track->track_type,
            'created_by' => create(User::class)->id,
        ]);

        /** @var Battle $battle */
        $battle = create(Battle::class, [
            'battle_state' => BattleStates::OPEN,
            'battle_type' => BattleTypes::TRACK,
        ]);
        $battle->tracks()->save($track);

        $this->makeRequest()
            ->assertSuccessful();

        $battle = Battle::first();
        $this->assertEquals(BattleStates::IN_PROGRESS->value, $battle->battle_state);
        $this->assertCount(2, $battle->tracks);

        $this->assertTrue($track->battles->contains($battle->id));
    }

    #[Test]
    public function when_creating_a_battle_for_a_track_it_wont_use_a_battle_already_created_if_has_different_track_type()
    {
        $this->track->forceFill([
            'track_type' => TrackTypes::BEAT,
        ])->saveQuietly();

        /** @var Track $track */
        $track = create(Track::class, [
            'track_type' => TrackTypes::SAMPLE,
            'created_by' => create(User::class)->id,
        ]);

        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);
        $battle->tracks()->save($track);

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(2, Battle::all());
    }

    #[Test]
    public function when_creating_a_battle_for_a_track_it_can_use_an_empty_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertTrue($this->track->battles->contains($battle->id));
    }

    #[Test]
    public function when_creating_a_battle_for_a_track_it_wont_let_battles_with_owns_tracks_it_will_create_another_battle_instead()
    {
        /** @var Track $track */
        $track = create(Track::class, ['created_by' => $this->user->id]);
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);
        $battle->tracks()->save($track);

        $this->makeRequest()
            ->assertSuccessful();

        $battles = Battle::all();
        $this->assertCount(2, $battles);
        $this->assertCount(1, $battles->first()->tracks);
        $this->assertCount(1, $battles->last()->tracks);
    }

    #[Test]
    public function when_creating_a_battle_you_can_specify_another_track_to_challenge_with()
    {
        $this->makeRequest(['opponent_id' => $this->otherTrack->id])
            ->assertSuccessful();

        /** @var Battle $battle */
        $battle = Battle::first();
        $this->assertEquals(BattleStates::IN_PROGRESS->value, $battle->battle_state);
        $this->assertEquals(BattleTypes::TRACK, $battle->battle_type);
        $this->assertCount(2, $battle->tracks);

        $this->assertTrue($battle->tracks->contains($this->track->id));
        $this->assertTrue($battle->tracks->contains($this->otherTrack->id));
    }

    #[Test]
    public function when_creating_a_battle_specifying_another_track_it_wont_accept_if_other_track_cannot_be_challenged()
    {
        $this->otherTrack
            ->forceFill(['can_challenge' => false])
            ->saveQuietly();

        $this->makeRequest(['opponent_id' => $this->otherTrack->id])
            ->assertUnprocessable();
    }

    #[Test]
    public function when_creating_a_battle_specifying_another_track_it_wont_accept_if_other_track_belongs_to_the_logged_user()
    {
        $this->otherTrack
            ->forceFill(['created_by' => $this->user->id])
            ->saveQuietly();

        $this->makeRequest(['opponent_id' => $this->otherTrack->id])
            ->assertUnprocessable();
    }

    #[Test]
    public function when_creating_a_battle_specifying_another_track_it_accept_if_other_track_has_different_type()
    {
        $this->otherTrack
            ->forceFill(['track_type' => TrackTypes::SAMPLE])
            ->saveQuietly();

        $this->makeRequest(['opponent_id' => $this->otherTrack->id])
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_track_that_is_already_looking_for_a_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);
        $battle->tracks()->save($this->track);

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_track_that_is_already_fighting_in_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS]);
        $battle->tracks()->save($this->track);

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_track_if_track_cannot_be_challenged()
    {
        $this->track
            ->forceFill(['can_challenge' => false])
            ->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_track_of_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        $this->track->created_by = $otherUser->id;
        $this->track->save();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_track_that_is_already_in_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, [
            'battle_state' => BattleStates::IN_PROGRESS,
            'battle_type' => BattleTypes::TRACK,
        ]);
        /** @var Track $foreignTrack */
        $foreignTrack = create(Track::class, [
            'created_by' => create(User::class)->id,
            'track_type' => TrackTypes::BEAT,
            'can_challenge' => true,
        ]);
        $battle->tracks()->saveMany([$this->otherTrack, $foreignTrack]);

        $this->makeRequest(['opponent_id' => $this->otherTrack->id])
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_to_non_authenticated_user()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    #[Test]
    public function it_wont_let_create_a_battle_if_user_has_not_enough_points()
    {
        $this->user->forceFill([
            'points' => 0,
        ])->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('battles.store'), array_merge([
            'id' => $this->track->id,
            'type' => 'track',
        ], $attributes));
    }
}
