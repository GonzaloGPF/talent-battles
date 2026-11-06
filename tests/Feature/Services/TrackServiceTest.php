<?php

namespace Tests\Feature\Services;

use App\Enums\ActivityTypes;
use App\Enums\TrackTypes;
use App\Jobs\CreateWatermark;
use App\Jobs\HandleActivity;
use App\Services\TrackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrackService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Event::fake();
        $this->service = resolve(TrackService::class);
    }

    #[Test]
    public function it_can_create_a_track()
    {
        $track = $this->service->createTrack([
            'track_type' => TrackTypes::BEAT,
            'name' => 'Some Name',
        ], UploadedFile::fake()->create('some.mp3'));

        $this->assertEquals('Some Name', $track->name);
        $this->assertEquals(TrackTypes::BEAT, $track->track_type);
        $this->assertEquals('some', $track->audio->name);

    }

    #[Test]
    public function when_creating_a_track_it_dispatches_handle_activity_job()
    {
        $this->service->createTrack([
            'track_type' => TrackTypes::BEAT,
            'name' => 'Some Name',
        ], UploadedFile::fake()->create('some.mp3'));

        Queue::assertPushed(HandleActivity::class, function (HandleActivity $job) {
            return $job->activityType === ActivityTypes::NEW_TRACK;
        });
    }

    #[Test]
    public function it_can_create_a_track_with_an_image()
    {
        $track = $this->service->createTrack([
            'track_type' => TrackTypes::BEAT,
            'name' => 'Some Name',
        ], UploadedFile::fake()->create('some.mp3'), UploadedFile::fake()->image('test.webp'));

        $this->assertEquals('test', $track->image->name);
    }

    #[Test]
    public function when_creating_a_track_user_can_specify_sell_option()
    {
        $track = $this->service->createTrack([
            'track_type' => TrackTypes::BEAT,
            'name' => 'Some Name',
            'sell' => true,
            'price' => 123,
        ], UploadedFile::fake()->create('some.mp3'), null, UploadedFile::fake()->create('untagged.mp3'));

        $this->assertEquals('some', $track->audio->name);

        $this->assertEquals('Some Name', $track->product->name);
        $this->assertEquals(123, $track->product->price);
        $this->assertEquals('untagged', $track->product->file->name);
    }

    #[Test]
    public function when_creating_a_track_user_can_specify_sell_option_with_exclusive()
    {
        $track = $this->service->createTrack([
            'track_type' => TrackTypes::BEAT,
            'name' => 'Some Name',
            'sell' => true,
            'exclusive' => true,
            'price' => 123,
        ], UploadedFile::fake()->create('some.mp3'), null, UploadedFile::fake()->create('untagged.mp3'));

        $this->assertTrue($track->product->exclusive);
    }

    #[Test]
    public function when_creating_a_track_with_sell_option_and_can_auto_tag_it_will_dispatch_create_watermark_job()
    {
        Queue::fake();

        $this->service = resolve(TrackService::class);

        $track = $this->service->createTrack([
            'track_type' => TrackTypes::BEAT,
            'name' => 'Some Name',
            'sell' => true,
            'auto_tag' => true,
            'price' => 123,
        ], UploadedFile::fake()->create('file.mp3'));

        Queue::assertPushed(CreateWatermark::class, function (CreateWatermark $job) {
            return ! is_null($job->delay);
        });
    }
}
