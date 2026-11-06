<?php

namespace Tests\Feature\Filters;

use App\Enums\BattleStates;
use App\Enums\Genres;
use App\Enums\Instruments;
use App\Enums\Thematics;
use App\Enums\TrackTypes;
use App\Models\Album;
use App\Models\Battle;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        create(Track::class, [], 3);
    }

    #[Test]
    public function it_can_filter_tracks_by_name()
    {
        create(Track::class, ['name' => 'Test']);

        $tracks = Track::filter(['name' => 'Test'])->get();

        $this->assertCount(1, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_genres()
    {
        Track::query()->update(['genres' => null]);
        create(Track::class, ['genres' => [Genres::HIP_HOP]]);

        $tracks = Track::filter(['genres' => [Genres::HIP_HOP->value]])->get();

        $this->assertCount(1, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_instruments()
    {
        Track::query()->update(['instruments' => null]);
        create(Track::class, ['instruments' => [Instruments::BASS]]);

        $tracks = Track::filter(['instruments' => [Instruments::BASS->value]])->get();

        $this->assertCount(1, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_track_types()
    {
        Track::query()->update(['track_type' => TrackTypes::BEAT]);

        create(Track::class, ['track_type' => TrackTypes::SAMPLE]);

        $tracks = Track::filter(['track_types' => [TrackTypes::SAMPLE]])->get();

        $this->assertCount(1, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_bpm_range()
    {
        Track::query()->update(['bpm' => 0]);

        create(Track::class, ['bpm' => 80]);
        create(Track::class, ['bpm' => 100]);
        create(Track::class, ['bpm' => 150]);

        $tracks = Track::filter(['min_bpm' => 90])->get();

        $this->assertCount(2, $tracks);

        $tracks = Track::filter(['max_bpm' => 90])->get();

        $this->assertCount(4, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_duration_range()
    {
        Track::query()->update(['duration' => 0]);

        create(Track::class, ['duration' => 80]);
        create(Track::class, ['duration' => 100]);
        create(Track::class, ['duration' => 150]);

        $tracks = Track::filter(['min_duration' => 90])->get();

        $this->assertCount(2, $tracks);

        $tracks = Track::filter(['max_duration' => 90])->get();

        $this->assertCount(4, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_created_by()
    {
        $user = create(User::class);

        create(Track::class, ['created_by' => $user->id]);

        $tracks = Track::filter(['created_by' => [$user->id]])->get();

        $this->assertCount(1, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_thematic()
    {
        Track::query()->update(['thematic' => null]);

        create(Track::class, ['thematic' => Thematics::NATURE]);

        $tracks = Track::filter(['thematics' => [Thematics::NATURE]])->get();

        $this->assertCount(1, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_promoted()
    {
        /** @var Track $track1 */
        $track1 = create(Track::class);

        create(Product::class, [
            'promoted' => today(),
            'orderable_type' => Track::class,
            'orderable_id' => $track1->id,
        ]);

        /** @var Track $track2 */
        $track2 = create(Track::class);

        create(Product::class, [
            'promoted' => today()->addMonth(),
            'orderable_type' => Track::class,
            'orderable_id' => $track2->id,
        ]);

        /** @var Track $track3 */
        $track3 = create(Track::class);

        create(Product::class, [
            'promoted' => today()->subMonth(),
            'orderable_type' => Track::class,
            'orderable_id' => $track3->id,
        ]);

        $tracks = Track::filter(['promoted' => true])->get();

        $this->assertCount(2, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_can_challenge()
    {
        Track::query()->update(['can_challenge' => false]);

        create(Track::class, ['can_challenge' => true]);

        $tracks = Track::filter(['can_challenge' => true])->get();

        $this->assertCount(1, $tracks);

        $tracks = Track::filter(['can_challenge' => false])->get();

        $this->assertCount(3, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_without_open_battles()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);
        /** @var Track $lookingForBattle */
        $lookingForBattle = create(Track::class);
        $lookingForBattle->battles()->save($battle);

        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS]);
        /** @var Track $alreadyInBattle */
        $alreadyInBattle = create(Track::class);
        $alreadyInBattle->battles()->save($battle);

        $trackIds = Track::filter(['without_open_battles' => true])
            ->get()
            ->pluck('id')
            ->toArray();

        $this->assertCount(3, $trackIds);
        $this->assertNotInArray($lookingForBattle->id, $trackIds);
        $this->assertNotInArray($alreadyInBattle->id, $trackIds);
    }

    #[Test]
    public function it_can_filter_tracks_by_exclusive()
    {
        /** @var Track $track1 */
        $track1 = create(Track::class);
        create(Product::class, [
            'orderable_type' => Track::class,
            'orderable_id' => $track1->id,
            'exclusive' => true,
        ]);
        create(Product::class, [
            'orderable_type' => Track::class,
            'orderable_id' => $track1->id,
            'exclusive' => false,
        ]);
        /** @var Track $track2 */
        $track2 = create(Track::class);
        create(Product::class, [
            'orderable_type' => Track::class,
            'orderable_id' => $track2->id,
            'exclusive' => false,
        ]);

        $tracks = Track::filter(['exclusive' => true])->get();

        $this->assertCount(1, $tracks);

        $tracks = Track::filter(['exclusive' => false])->get();

        $this->assertCount(2, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_in_sale()
    {
        /** @var Track $track */
        $track = create(Track::class);
        create(Product::class, [
            'orderable_type' => Track::class,
            'orderable_id' => $track->id,
        ]);

        $tracks = Track::filter(['in_sale' => true])->get();

        $this->assertCount(1, $tracks);

        $tracks = Track::filter(['in_sale' => false])->get();

        $this->assertCount(3, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_sold()
    {
        /** @var Track $track1 */
        $track1 = create(Track::class);
        create(Product::class, [
            'orderable_type' => Track::class,
            'orderable_id' => $track1->id,
            'sales' => 0,
        ]);
        create(Product::class, [
            'orderable_type' => Track::class,
            'orderable_id' => $track1->id,
            'sales' => 1,
        ]);
        /** @var Track $track2 */
        $track2 = create(Track::class);
        create(Product::class, [
            'orderable_type' => Track::class,
            'orderable_id' => $track2->id,
            'sales' => 1,
        ]);

        $tracks = Track::filter(['sold' => true])->get();

        $this->assertCount(2, $tracks);

        $tracks = Track::filter(['sold' => false])->get();

        $this->assertCount(1, $tracks);
    }

    #[Test]
    public function it_can_filter_tracks_by_tags()
    {
        Track::query()->update(['tags' => null]);

        create(Track::class, ['tags' => ['some', 'tags', 'test']]);

        $data = Track::filter(['tags' => ['test']])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_tracks_by_album()
    {
        Track::query()->update(['album_id' => null]);

        /** @var Album $album */
        $album = create(Album::class);
        create(Track::class, ['album_id' => $album->id]);

        $data = Track::filter(['album' => [$album->id]])->get();

        $this->assertCount(1, $data);
    }
}
