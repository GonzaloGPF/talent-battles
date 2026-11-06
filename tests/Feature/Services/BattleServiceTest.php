<?php

namespace Tests\Feature\Services;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\TrackTypes;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use App\Services\BattleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BattleServiceTest extends TestCase
{
    use RefreshDatabase;

    private BattleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->service = resolve(BattleService::class);
    }

    #[Test]
    public function it_can_create_a_battle_for_two_tracks()
    {
        /** @var Track $track1 */
        $track1 = create(Track::class, ['track_type' => TrackTypes::BEAT]);
        /** @var Track $track2 */
        $track2 = create(Track::class, ['track_type' => TrackTypes::BEAT]);

        $battle = $this->service->createForTracks($track1, $track2);

        $this->assertEquals(BattleStates::IN_PROGRESS->value, $battle->battle_state);
        $this->assertEquals(BattleTypes::TRACK, $battle->battle_type);
        $this->assertCount(2, $battle->tracks);
    }

    #[Test]
    public function when_creating_a_battle_for_two_tracks_it_can_reuse_existing_battle_if_one_track_was_looking_for_a_battle()
    {
        /** @var Track $track1 */
        $track1 = create(Track::class, ['track_type' => TrackTypes::BEAT]);
        /** @var Track $track2 */
        $track2 = create(Track::class, ['track_type' => TrackTypes::BEAT]);
        /** @var Battle $existingBattle */
        $existingBattle = create(Battle::class, [
            'battle_type' => BattleTypes::TRACK->value,
            'battle_state' => BattleStates::OPEN,
        ]);
        $existingBattle->tracks()->save($track2);

        $battle = $this->service->createForTracks($track1, $track2);

        $this->assertEquals($existingBattle->id, $battle->id);
        $this->assertEquals(BattleStates::IN_PROGRESS->value, $battle->battle_state);
        $this->assertEquals(BattleTypes::TRACK, $battle->battle_type);
        $this->assertCount(2, $battle->tracks);
    }

    #[Test]
    public function it_can_create_a_battle_for_a_given_track()
    {
        /** @var Track $track */
        $track = create(Track::class, ['track_type' => TrackTypes::BEAT]);

        $battle = $this->service->createForTrack($track);

        $this->assertEquals(BattleStates::OPEN->value, $battle->battle_state);
        $this->assertEquals(BattleTypes::TRACK, $battle->battle_type);
        $this->assertCount(1, $battle->tracks);
    }

    #[Test]
    public function when_creating_a_battle_for_a_given_track_it_can_reuse_existing_battle_with_another_track_waiting_opponent()
    {
        /** @var Track $track1 */
        $track1 = create(Track::class, ['track_type' => TrackTypes::BEAT]);
        /** @var Track $track2 */
        $track2 = create(Track::class, [
            'track_type' => TrackTypes::BEAT,
            'created_by' => create(User::class)->id,
        ]);

        /** @var Battle $existingBattle */
        $existingBattle = create(Battle::class, [
            'battle_state' => BattleStates::OPEN,
            'battle_type' => BattleTypes::TRACK->value,
        ]);
        $existingBattle->tracks()->save($track2);

        $battle = $this->service->createForTrack($track1);

        $this->assertEquals($existingBattle->id, $battle->id);
        $this->assertEquals(BattleStates::IN_PROGRESS->value, $battle->battle_state);
        $this->assertEquals(BattleTypes::TRACK, $battle->battle_type);
        $this->assertCount(2, $battle->tracks);
    }

    #[Test]
    public function when_creating_a_battle_for_a_given_track_it_can_reuse_existing_battle_without_any_tracks()
    {
        /** @var Track $track1 */
        $track1 = create(Track::class, ['track_type' => TrackTypes::BEAT]);

        /** @var Battle $existingBattle */
        $existingBattle = create(Battle::class, [
            'battle_state' => BattleStates::OPEN,
            'battle_type' => BattleTypes::TRACK->value,
        ]);

        $battle = $this->service->createForTrack($track1);

        $this->assertEquals($existingBattle->id, $battle->id);
        $this->assertEquals(BattleStates::OPEN->value, $battle->battle_state);
        $this->assertEquals(BattleTypes::TRACK, $battle->battle_type);
        $this->assertCount(1, $battle->tracks);
    }
}
