<?php

namespace Tests\Feature\Votes;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\Levels;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateBattleVoteTest extends TestCase
{
    use RefreshDatabase;

    private Battle $battle;

    private Track $trackA;

    private Track $trackB;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class, [
            'level' => Levels::NOVICE,
            'experience' => 0,
            'verified_at' => today(),
        ]);
        $this->battle = create(Battle::class, [
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::IN_PROGRESS,
        ]);
        $this->trackA = create(Track::class, ['created_by' => create(User::class)->id]);
        $this->trackB = create(Track::class, ['created_by' => create(User::class)->id]);

        $this->battle->tracks()->saveMany([$this->trackA, $this->trackB]);

        $this->signIn($this->user);
    }

    #[Test]
    public function authenticated_user_can_vote_a_track()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(1, $this->trackA->votes);
        $this->assertCount(1, $this->battle->votes);
        $this->assertEquals(1, $this->battle->votes->sum('value'));
    }

    #[Test]
    public function when_user_does_a_vote_it_will_give_points_depending_on_its_level()
    {
        $this->user->forceFill([
            'level' => Levels::MEDIUM,
        ])->saveQuietly();

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals(3, $this->battle->votes->sum('value'));
    }

    #[Test]
    public function after_voting_user_gets_awarded()
    {
        /** @var User $user */
        $user = auth()->user();
        $user->forceFill([
            'points' => 0,
        ])->saveQuietly();

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals(config('company.points.awards.do_vote'), $user->fresh()->points);
    }

    #[Test]
    public function it_wont_let_vote_twice()
    {
        create(Vote::class, [
            'created_by' => auth()->id(),
            'battle_id' => $this->battle->id,
            'votable_id' => $this->trackA->id,
            'votable_type' => get_class($this->trackA),
        ]);

        $this->makeRequest()
            ->assertUnprocessable();

        $this->assertCount(1, $this->trackA->votes);
        $this->assertCount(1, $this->battle->votes);
    }

    #[Test]
    public function non_authenticated_user_cannot_vote()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    #[Test]
    public function it_wont_let_vote_a_battle_with_own_track()
    {
        $this->trackA
            ->forceFill(['created_by' => auth()->id()])
            ->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_vote_a_battle_that_is_open()
    {
        $this->battle
            ->forceFill(['battle_state' => BattleStates::OPEN])
            ->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_vote_a_battle_that_is_finished()
    {
        $this->battle
            ->forceFill(['battle_state' => BattleStates::FINISHED])
            ->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_vote_a_battle_specifying_a_track_that_doesnt_belong_to_the_battle()
    {
        /** @var Track $otherCreation */
        $otherCreation = create(Track::class);

        $this->makeRequest(['model_id' => $otherCreation->id])
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_vote_battle_if_user_is_not_verified()
    {
        $this->user
            ->forceFill(['verified_at' => null])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('battles.votes.store', $this->battle->id), array_merge([
            'model_id' => $this->trackA->id,
            'model_type' => 'track',
        ], $attributes));
    }
}
