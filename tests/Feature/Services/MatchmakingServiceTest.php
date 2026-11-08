<?php

namespace Tests\Feature\Services;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\DesignTypes;
use App\Enums\Roles;
use App\Enums\TrackTypes;
use App\Events\BattleInProgress;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use App\Services\MatchmakingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class MatchmakingServiceTest extends TestCase
{
    use RefreshDatabase;

    private MatchmakingService $matchmakingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchmakingService = resolve(MatchmakingService::class);
        config(['company.battles_in_progress' => 1]);
    }

    #[Test]
    public function it_fills_the_battles_in_progress_up_to_the_target()
    {
        config(['company.battles_in_progress' => 2]);
        $this->createBeats(6);

        $created = $this->matchmakingService->fillBattlesInProgress();

        $this->assertEquals(2, $created);
        $this->assertEquals(2, Battle::areInProgress()->count());
    }

    #[Test]
    public function it_creates_no_battles_when_the_target_is_already_reached()
    {
        $this->createBattle($this->createBeats(2), BattleStates::IN_PROGRESS);
        $this->createBeats(2);

        $this->assertEquals(0, $this->matchmakingService->fillBattlesInProgress());
    }

    #[Test]
    public function battles_are_created_by_the_talent_battles_account()
    {
        $platformUser = create(User::class, ['role' => Roles::ADMIN]);
        create(User::class, ['role' => Roles::ADMIN]);
        $this->createBeats(2);

        $this->matchmakingService->fillBattlesInProgress();

        $this->assertEquals($platformUser->id, Battle::sole()->created_by);
    }

    #[Test]
    public function battles_start_through_the_state_machine()
    {
        $beats = $this->createBeats(2);
        Event::fake([BattleInProgress::class]);

        $this->matchmakingService->fillBattlesInProgress();

        $battle = Battle::sole();
        $this->assertEquals(BattleStates::IN_PROGRESS->value, $battle->battle_state);
        $this->assertEqualsCanonicalizing($beats->pluck('id')->all(), $battle->tracks->modelKeys());
        Event::assertDispatched(BattleInProgress::class);
    }

    #[Test]
    public function creations_whose_owners_do_not_accept_challenges_are_left_out()
    {
        $this->createBeats(1);
        $this->createBeats(1, ['can_challenge' => false]);

        $this->assertEquals(0, $this->matchmakingService->fillBattlesInProgress());
    }

    #[Test]
    public function two_creations_of_the_same_owner_never_face_each_other()
    {
        $this->createBeats(2, ['created_by' => create(User::class)->id]);

        $this->assertEquals(0, $this->matchmakingService->fillBattlesInProgress());
    }

    #[Test]
    public function only_creations_of_the_same_kind_face_each_other()
    {
        $this->createBeats(1);
        $this->createBeats(1, ['track_type' => TrackTypes::SAMPLE]);
        $this->createDesigns(1);

        $this->assertEquals(0, $this->matchmakingService->fillBattlesInProgress());
    }

    #[Test]
    public function a_creation_of_another_kind_does_not_take_the_place_of_an_opponent()
    {
        $beat = $this->createBeats(1);
        $sample = $this->createBeats(1, ['track_type' => TrackTypes::SAMPLE]);
        $this->createFinishedBattles($sample, 1);
        $opponent = $this->createBeats(1);
        $this->createFinishedBattles($opponent, 2);

        $this->matchmakingService->fillBattlesInProgress();

        $battle = Battle::areInProgress()->sole();
        $this->assertEqualsCanonicalizing($beat->merge($opponent)->pluck('id')->all(), $battle->tracks->modelKeys());
    }

    #[Test]
    public function designs_face_designs()
    {
        $designs = $this->createDesigns(2);

        $this->matchmakingService->fillBattlesInProgress();

        $battle = Battle::sole();
        $this->assertEquals(BattleTypes::DESIGN, $battle->battle_type);
        $this->assertEqualsCanonicalizing($designs->pluck('id')->all(), $battle->designs->modelKeys());
    }

    #[Test]
    public function creations_already_in_a_battle_are_left_out()
    {
        config(['company.battles_in_progress' => 2]);
        $this->createBattle($this->createBeats(2), BattleStates::IN_PROGRESS);
        $this->createBattle($this->createBeats(1), BattleStates::OPEN);
        $this->createBeats(1);

        $this->assertEquals(0, $this->matchmakingService->fillBattlesInProgress());
    }

    #[Test]
    public function creations_that_already_faced_each_other_are_not_paired_again()
    {
        $this->createBattle($this->createBeats(2), BattleStates::FINISHED);

        $this->assertEquals(0, $this->matchmakingService->fillBattlesInProgress());
    }

    #[Test]
    public function creations_with_fewer_battles_are_paired_first()
    {
        $veteran = $this->createBeats(1);
        $this->createFinishedBattles($veteran, 2);
        $newcomers = $this->createBeats(2);

        $this->matchmakingService->fillBattlesInProgress();

        $battle = Battle::areInProgress()->sole();
        $this->assertEqualsCanonicalizing($newcomers->pluck('id')->all(), $battle->tracks->modelKeys());
    }

    #[Test]
    public function a_battle_that_fails_to_start_is_not_left_behind()
    {
        $this->createBeats(2);
        Event::listen(BattleInProgress::class, fn () => throw new RuntimeException('Notifications are down'));

        $this->assertEquals(0, $this->matchmakingService->fillBattlesInProgress());
        $this->assertEquals(0, Battle::count());
    }

    #[Test]
    public function it_creates_no_battles_while_another_run_holds_the_lock()
    {
        $this->createBeats(2);
        Cache::lock(MatchmakingService::class, 300)->get();

        $this->assertEquals(0, $this->matchmakingService->fillBattlesInProgress());
        $this->assertEquals(0, Battle::count());
    }

    /**
     * Beats that accept challenges, each one from a different owner unless the attributes say otherwise.
     *
     * @return Collection<int, Track>
     */
    private function createBeats(int $count, array $attributes = []): Collection
    {
        return collect(range(1, $count))
            ->map(fn () => create(Track::class, [
                'created_by' => create(User::class, ['role' => Roles::USER])->id,
                'track_type' => TrackTypes::BEAT,
                'can_challenge' => true,
                ...$attributes,
            ]));
    }

    /**
     * Logos that accept challenges, each one from a different owner.
     *
     * @return Collection<int, Design>
     */
    private function createDesigns(int $count): Collection
    {
        return collect(range(1, $count))
            ->map(fn () => create(Design::class, [
                'created_by' => create(User::class, ['role' => Roles::USER])->id,
                'design_type' => DesignTypes::LOGO,
                'can_challenge' => true,
            ]));
    }

    /**
     * Past battles of a track, against opponents that do not accept challenges any more.
     *
     * @param  Collection<int, Track>  $track
     */
    private function createFinishedBattles(Collection $track, int $count): void
    {
        foreach (range(1, $count) as $ignored) {
            $opponent = $this->createBeats(1, ['track_type' => $track->first()->track_type, 'can_challenge' => false]);

            $this->createBattle($track->merge($opponent), BattleStates::FINISHED);
        }
    }

    /**
     * @param  Collection<int, Track>  $tracks
     */
    private function createBattle(Collection $tracks, BattleStates $state): Battle
    {
        $battle = create(Battle::class, [
            'battle_state' => $state->value,
            'battle_type' => BattleTypes::TRACK,
        ]);

        $battle->tracks()->attach($tracks->pluck('id')->all());

        return $battle;
    }
}
