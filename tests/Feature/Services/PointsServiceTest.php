<?php

namespace Tests\Feature\Services;

use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PointsServiceTest extends TestCase
{
    use RefreshDatabase;

    private PointsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(PointsService::class);
        User::flushEventListeners();
    }

    #[Test]
    public function it_can_earn_points_to_a_given_user()
    {
        /** @var User $user */
        $user = create(User::class, [
            'points' => 0,
            'experience' => 0,
        ]);

        $user = $this->service->earnPoints(10, $user);

        $this->assertEquals(10, $user->points);
        $this->assertEquals(10, $user->experience);
    }

    #[Test]
    public function when_earning_points_it_can_specify_to_avoid_giving_experience()
    {
        /** @var User $user */
        $user = create(User::class, [
            'points' => 0,
            'experience' => 0,
        ]);

        $user = $this->service->earnPoints(10, $user, true);

        $this->assertEquals(10, $user->points);
        $this->assertEquals(0, $user->experience);
    }

    #[Test]
    public function it_can_spend_points()
    {
        /** @var User $user */
        $user = create(User::class, [
            'points' => 10,
            'experience' => 0,
        ]);

        $user = $this->service->spendPoints(10, $user);

        $this->assertEquals(0, $user->points);
        $this->assertEquals(5, $user->experience);
    }

    #[Test]
    public function it_gives_points_to_participants_of_a_draw_battle()
    {
        /** @var User $user */
        $user = create(User::class, ['points' => 0]);
        /** @var Track $track */
        $track = create(Track::class, ['created_by' => $user->id]);
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_type' => BattleTypes::TRACK]);

        $battle->tracks()->save($track);

        $this->service->giveDrawPoints($battle);

        $this->assertEquals(config('company.points.awards.draw_battle'), $user->fresh()->points);
    }

    #[Test]
    public function it_gives_points_to_participants_of_a_won_battle()
    {
        /** @var User $user */
        $user = create(User::class, ['points' => 0]);
        /** @var Track $track */
        $track = create(Track::class, ['created_by' => $user->id]);
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_type' => BattleTypes::TRACK]);

        $battle->winner()
            ->associate($track)
            ->save();

        $this->service->givePointsToWinner($battle);

        $this->assertEquals(config('company.points.awards.win_battle'), $user->fresh()->points);
    }

    #[Test]
    public function it_gives_points_to_voters_of_winner_of_a_finished_battle()
    {
        /** @var User $user */
        $user = create(User::class, ['points' => 0]);
        /** @var Track $track */
        $track = create(Track::class, ['created_by' => $user->id]);
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_type' => BattleTypes::TRACK]);

        /** @var User $voter */
        $voter = create(User::class, ['points' => 0]);
        create(Vote::class, [
            'created_by' => $voter->id,
            'votable_id' => $track->id,
            'votable_type' => Track::class,
        ]);

        $battle->winner()
            ->associate($track)
            ->save();

        $this->service->givePointsToVoters($battle);

        $this->assertEquals(config('company.points.awards.do_winner_vote'), $voter->fresh()->points);
    }
}
