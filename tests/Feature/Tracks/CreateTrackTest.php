<?php

namespace Tests\Feature\Tracks;

use App\Enums\ActivityTypes;
use App\Enums\BattleTypes;
use App\Enums\FileTypes;
use App\Enums\Genres;
use App\Enums\TrackTypes;
use App\Jobs\CreateWatermark;
use App\Jobs\HandleActivity;
use App\Models\Battle;
use App\Models\File;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateTrackTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        Queue::fake();
        $this->user = $this->signIn();
    }

    #[Test]
    public function it_let_users_create_a_track_with_a_file()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $track = Track::first();

        $this->assertEquals('Some Name', $track->name);
        $this->assertEquals(100, $track->bpm);
        $this->assertEquals('Some Description', $track->description);

        tap($track->audio, function (File $file) {
            $this->assertEquals(FileTypes::AUDIO, $file->file_type);
            $this->assertEquals('my_audio.mp3', $file->fullName());
            $this->assertEquals('audio/mpeg', $file->mime);
        });

        Queue::assertPushed(HandleActivity::class, function (HandleActivity $job) {
            return ActivityTypes::NEW_TRACK->value === $job->activityType->value;
        });
    }

    #[Test]
    public function when_creating_a_track_it_can_specify_genres()
    {
        $this->makeRequest(['genres' => [Genres::AMBIENT, Genres::BLUES]])
            ->assertSuccessful();

        $track = Track::first();

        $this->assertInArray(Genres::AMBIENT->value, $track->genres->toArray());
    }

    #[Test]
    public function when_creating_a_track_it_can_upload_an_image_too()
    {
        $this->makeRequest([
            'image' => UploadedFile::fake()->image('test.png'),
        ])->assertSuccessful();

        /** @var Track $track */
        $track = Track::first();

        tap($track->image, function (File $file) {
            $this->assertEquals(FileTypes::IMAGE, $file->file_type);
            $this->assertEquals('test.png', $file->fullName());
            $this->assertEquals('image/png', $file->mime);
        });
    }

    #[Test]
    public function when_creating_a_track_it_creates_a_product_if_user_specifies_sell_option()
    {
        $this->makeRequest([
            'sell' => true,
            'price' => 100,
            'exclusive' => true,
            'file_to_sell' => UploadedFile::fake()->create('sale.mp3'),
        ])->assertSuccessful();

        $track = Track::first();

        $this->assertEquals(100, $track->product->price);
        $this->assertTrue($track->product->exclusive);

        tap($track->product->file, function (File $file) {
            $this->assertEquals(FileTypes::AUDIO, $file->file_type);
            $this->assertEquals('sale.mp3', $file->fullName());
            $this->assertEquals('audio/mpeg', $file->mime);
        });
    }

    #[Test]
    public function when_creating_a_track_it_can_generate_a_tagged_file_if_auto_tag_is_checked()
    {
        $this->makeRequest([
            'sell' => true,
            'price' => 100,
            'auto_tag' => true,
        ])->assertSuccessful();

        $track = Track::first();

        $this->assertNotNull($track->product->file);
        Queue::assertPushed(CreateWatermark::class, function (CreateWatermark $job) {
            return ! is_null($job->delay);
        });
    }

    #[Test]
    public function when_creating_a_track_it_wont_let_sell_if_no_price_is_specified()
    {
        $this->makeRequest([
            'sell' => true,
            'file_to_sell' => UploadedFile::fake()->create('track.mp3'),
        ])->assertUnprocessable();
    }

    #[Test]
    public function when_creating_a_track_it_can_specify_tags()
    {
        $this->makeRequest(['tags' => ['some', 'tag', 'test']])
            ->assertSuccessful();

        $track = Track::first();
        $this->assertCount(3, $track->tags);
    }

    #[Test]
    public function when_creating_a_track_it_adds_points_to_the_user()
    {
        /** @var User $user */
        $user = auth()->user();
        $user->forceFill([
            'points' => 0,
        ])->saveQuietly();

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals(config('company.points.awards.upload_creation'), $user->fresh()->points);
    }

    #[Test]
    public function non_authenticated_user_cant_create_tracks()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    #[Test]
    public function it_wont_let_create_more_than_three_tracks_if_account_is_not_verified()
    {
        $this->user
            ->forceFill(['verified_at' => null])
            ->saveQuietly();

        create(Track::class, ['created_by' => $this->user->id], 3);

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_let_create_more_than_three_tracks_if_account_is_verified()
    {
        $this->user
            ->forceFill(['verified_at' => now()])
            ->saveQuietly();

        create(Track::class, ['created_by' => $this->user->id], 3);

        $this->makeRequest()
            ->assertSuccessful();
    }

    #[Test]
    public function when_creating_a_track_user_can_create_a_battle_too()
    {
        $this->user
            ->forceFill(['points' => config('company.points.prices.create_battle')])
            ->saveQuietly();

        $this->makeRequest(['battle' => true])
            ->assertSuccessful();

        $battle = Battle::first();

        $this->assertEquals($this->user->id, $battle->created_by);
        $this->assertEquals(BattleTypes::TRACK, $battle->battle_type);
        $this->assertEquals(2, $this->user->fresh()->points);
    }

    #[Test]
    public function when_creating_a_track_user_it_wont_let_create_a_battle_if_user_does_not_have_enough_points()
    {
        $this->user
            ->forceFill(['points' => config('company.points.prices.create_battle') - 1])
            ->saveQuietly();

        $this->makeRequest(['battle' => true])
            ->assertUnprocessable();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('tracks.store'), array_merge([
            'track_type' => TrackTypes::BEAT->value,
            'name' => 'Some Name',
            'bpm' => 100,
            'description' => 'Some Description',
            'audio' => UploadedFile::fake()->create('my_audio.mp3'),
        ], $attributes));
    }
}
