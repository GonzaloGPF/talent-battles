<?php

namespace Tests\Feature\StateMachines;

use App\Enums\ActivityTypes;
use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\TrackTypes;
use App\Models\Activity;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;
use App\Notifications\BattleLost;
use App\Notifications\BattleStarted;
use App\Notifications\BattleWon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BattleStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private User $winnerUser;

    private Battle $battle;

    private Track $trackA;

    private Track $trackB;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        User::flushEventListeners();
        $this->winnerUser = create(User::class, [
            'points' => 0,
        ]);
        $this->battle = create(Battle::class, [
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::OPEN->value,
        ]);
        $this->trackA = create(Track::class, [
            'created_by' => $this->winnerUser->id,
            'track_type' => TrackTypes::BEAT,
        ]);
        $this->trackB = create(Track::class, [
            'created_by' => create(User::class, ['points' => 0])->id,
            'track_type' => TrackTypes::BEAT,
        ]);

        // TrackA is winning
        create(Vote::class, [
            'created_by' => create(User::class)->id,
            'votable_id' => $this->trackA->id,
            'votable_type' => Track::class,
            'value' => 10,
        ]);

        $this->battle->tracks()->saveMany([$this->trackA, $this->trackB]);
    }

    #[Test]
    public function it_cannot_jump_to_a_state_that_is_not_allowed_from_the_current_one()
    {
        $this->expectException(AuthorizationException::class);

        $this->battle->changeState(BattleStates::FINISHED);
    }

    #[Test]
    public function changing_to_the_current_state_does_nothing()
    {
        $this->battle->changeState(BattleStates::OPEN);

        $this->assertSame(BattleStates::OPEN->value, $this->battle->fresh()->battle_state);
    }

    #[Test]
    public function it_does_not_save_the_state_when_validation_fails()
    {
        $this->battle->tracks()->detach($this->trackB);

        try {
            $this->battle->changeState(BattleStates::IN_PROGRESS);
            $this->fail('The transition should have been rejected');
        } catch (ValidationException) {
            $this->assertSame(BattleStates::OPEN->value, $this->battle->fresh()->battle_state);
        }
    }

    #[Test]
    public function it_can_transition_track_battle_to_in_progress_from_open()
    {
        $this->battle->changeState(BattleStates::IN_PROGRESS);

        $this->assertEquals(BattleStates::IN_PROGRESS->value, $this->battle->battle_state);
    }

    #[Test]
    public function when_transitioning_to_in_progress_it_will_notify_all_participants_except_logged_user()
    {
        Notification::fake();

        $this->signIn($this->trackA->createdBy);

        $this->battle->changeState(BattleStates::IN_PROGRESS);

        Notification::assertSentTo($this->trackB->createdBy, function (BattleStarted $notification) {
            return $this->battle->id === $notification->battle->id;
        });
    }

    #[Test]
    public function it_can_transition_track_battle_to_finished_from_in_progress()
    {
        $this->battle->forceFill([
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ])->saveQuietly();

        $this->battle->changeState(BattleStates::FINISHED);

        $this->assertEquals(BattleStates::FINISHED->value, $this->battle->battle_state);
        $this->assertEquals($this->winnerUser->id, $this->battle->won_by);
    }

    #[Test]
    public function when_transitioning_to_finished_state_it_will_create_an_activity_for_win_battle()
    {
        $this->battle->forceFill([
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ])->saveQuietly();

        $this->battle->changeState(BattleStates::FINISHED);

        /** @var Activity $activity */
        $activity = Activity::first();

        $this->assertEquals(ActivityTypes::WIN_BATTLE, $activity->activity_type);
        //        $this->assertEquals($this->user->id, $activity->created_by);
        $this->assertEquals($this->battle->winner->created_by, $activity->receiver_id);
        $this->assertEquals($this->battle->id, $activity->target_id);
        $this->assertEquals(Battle::class, $activity->target_type);
    }

    #[Test]
    public function when_transitioning_to_finished_it_will_notify_to_winner_about_it()
    {
        Notification::fake();

        $this->battle->forceFill([
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ])->saveQuietly();

        $this->battle->changeState(BattleStates::FINISHED);

        Notification::assertSentTo($this->trackA->createdBy, function (BattleWon $notification) {
            return $notification->battle->id === $this->battle->id;
        });
    }

    #[Test]
    public function when_transitioning_to_finished_it_will_notify_to_loser_about_it()
    {
        Notification::fake();

        $this->battle->forceFill([
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ])->saveQuietly();

        $this->battle = $this->battle->changeState(BattleStates::FINISHED);

        Notification::assertSentTo($this->trackB->createdBy, function (BattleLost $notification) {
            return $notification->battle->id === $this->battle->id;
        });
    }

    #[Test]
    public function when_transitioning_to_finished_it_will_give_points_to_the_winner()
    {
        $this->battle->forceFill([
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ])->saveQuietly();

        $this->battle->changeState(BattleStates::FINISHED);

        $this->assertEquals(config('company.points.awards.win_battle'), $this->winnerUser->fresh()->points);
    }

    #[Test]
    public function when_transitioning_to_finished_it_will_give_points_to_the_all_participants_if_its_a_draw()
    {
        Vote::query()->delete();

        $this->battle->forceFill([
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ])->saveQuietly();

        $this->battle->changeState(BattleStates::FINISHED);

        $this->battle
            ->getParticipants()
            ->each(fn (User $user) => $this->assertEquals(config('company.points.awards.draw_battle'), $user->points));
    }

    #[Test]
    public function when_transitioning_to_finished_it_will_give_extra_points_to_all_voters_if_they_voted_for_the_winning_creation()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class, ['points' => 0]);

        create(Vote::class, [
            'created_by' => $otherUser->id,
            'votable_id' => $this->trackA->id,
            'votable_type' => Track::class,
            'value' => 10,
        ]);

        $this->battle->forceFill([
            'battle_state' => BattleStates::IN_PROGRESS->value,
        ])->saveQuietly();

        $this->battle->changeState(BattleStates::FINISHED);
        $this->assertEquals(config('company.points.awards.do_winner_vote'), $otherUser->fresh()->points);
    }

    #[Test]
    public function it_wont_transition_track_battle_to_in_progress_from_open_if_tracks_has_different_types()
    {
        $this->battle
            ->forceFill(['battle_type' => BattleTypes::TRACK])
            ->saveQuietly();

        $this->trackB
            ->forceFill(['track_type' => TrackTypes::SAMPLE])
            ->saveQuietly();

        $this->expectException(ValidationException::class);

        $this->battle = $this->battle->changeState(BattleStates::IN_PROGRESS);

        $this->assertEquals(BattleStates::OPEN->value, $this->battle->battle_state);
    }
}
