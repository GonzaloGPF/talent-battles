<?php

namespace Tests\Feature\Stats;

use App\Enums\TrackTypes;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewTrackSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
    }

    #[Test]
    public function it_let_view_track_summary_stats_data()
    {
        create(Track::class, ['track_type' => TrackTypes::BEAT]);
        create(Track::class, ['track_type' => TrackTypes::BEAT]);
        create(Track::class, ['track_type' => TrackTypes::SAMPLE]);
        create(Track::class, [ // Should be ignored
            'track_type' => TrackTypes::BEAT,
            'created_by' => create(User::class)->id,
        ]);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals(modelTitle('track', true), $data['datasets'][0]['label']);
        $this->assertEquals([2, 1], $data['datasets'][0]['data']);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('stats.tracks.summary'));
    }
}
