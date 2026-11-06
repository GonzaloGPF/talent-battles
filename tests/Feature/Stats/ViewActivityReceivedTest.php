<?php

namespace Tests\Feature\Stats;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewActivityReceivedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
    }

    #[Test]
    public function it_let_view_activity_received_stats_data()
    {
        /** @var Track $track */
        $track = create(Track::class);

        create(Activity::class, [
            'receiver_id' => auth()->id(),
            'created_by' => create(User::class)->id,
            'activity_type' => ActivityTypes::DO_LIKE,
            'target_id' => $track->id,
            'target_type' => Track::class,
            'created_at' => today()->subMonths(6),
        ]);

        create(Activity::class, [
            'receiver_id' => auth()->id(),
            'created_by' => create(User::class)->id,
            'activity_type' => ActivityTypes::DO_COMMENT,
            'target_id' => $track->id,
            'target_type' => Track::class,
            'created_at' => today()->subMonths(5),
        ]);

        create(Activity::class, [
            'receiver_id' => auth()->id(),
            'created_by' => create(User::class)->id,
            'activity_type' => ActivityTypes::DO_FAVORITE,
            'target_id' => $track->id,
            'target_type' => Track::class,
            'created_at' => today()->subMonths(4),
        ]);

        create(Activity::class, [
            'receiver_id' => auth()->id(),
            'created_by' => create(User::class)->id,
            'activity_type' => ActivityTypes::DO_DOWNLOAD,
            'target_id' => $track->id,
            'target_type' => Track::class,
            'created_at' => today()->subMonths(3),
        ]);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals(modelTitle('comment', true), $data['datasets'][0]['label']);
        //        $this->assertEquals([0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0], $data['datasets'][0]['data']);

        $this->assertEquals(modelTitle('like', true), $data['datasets'][1]['label']);
        //        $this->assertEquals([0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0], $data['datasets'][1]['data']);

        $this->assertEquals(modelTitle('favorite', true), $data['datasets'][2]['label']);
        //        $this->assertEquals([0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0], $data['datasets'][2]['data']);

        $this->assertEquals(modelTitle('download', true), $data['datasets'][3]['label']);
        //        $this->assertEquals([0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0], $data['datasets'][3]['data']);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('stats.activities.received'));
    }
}
