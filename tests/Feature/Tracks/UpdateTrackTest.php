<?php

namespace Tests\Feature\Tracks;

use App\Enums\FileTypes;
use App\Enums\Genres;
use App\Enums\Roles;
use App\Enums\TrackTypes;
use App\Models\File;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateTrackTest extends TestCase
{
    use RefreshDatabase;

    private Track $track;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->user = $this->signIn();
        $this->track = create(Track::class, [
            'created_by' => $this->user->id,
            'track_type' => TrackTypes::BEAT,
        ]);
        create(File::class, [
            'created_by' => $this->user->id,
            'fileable_id' => $this->track->id,
            'fileable_type' => Track::class,
            'file_type' => FileTypes::AUDIO,
            'mime' => 'audio/mpeg',
            'path' => generateTestFile('test', 'mp3'),
            'name' => 'test',
        ]);
        create(File::class, [
            'created_by' => $this->user->id,
            'fileable_id' => $this->track->id,
            'fileable_type' => Track::class,
            'file_type' => FileTypes::IMAGE,
            'mime' => 'image/png',
            'path' => generateTestFile('test', 'png'),
            'name' => 'test',
        ]);
    }

    #[Test]
    public function it_let_users_update_its_tracks()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals('Edited', $this->track->fresh()->name);
    }

    #[Test]
    public function when_updating_a_track_it_let_modify_file()
    {
        $this->makeRequest([
            'audio' => UploadedFile::fake()->create('updated.mp3'),
        ])->assertSuccessful();

        $this->assertEquals('updated.mp3', $this->track->audio->fullName());
    }

    #[Test]
    public function when_updating_a_track_it_let_modify_image()
    {
        $this->makeRequest([
            'image' => UploadedFile::fake()->create('new.webp'),
        ])->assertSuccessful();

        $this->assertEquals('new.webp', $this->track->image->fullName());
    }

    #[Test]
    public function when_updating_a_track_it_let_add_image_if_track_doesnt_have_one()
    {
        $this->track->image()->delete();

        $this->makeRequest([
            'image' => UploadedFile::fake()->create('new.webp'),
        ])->assertSuccessful();

        $this->assertEquals('new.webp', $this->track->image->fullName());
    }

    #[Test]
    public function it_wont_modify_file_if_it_is_in_sale()
    {
        create(Product::class, [
            'orderable_type' => Track::class,
            'orderable_id' => $this->track->id,
        ]);

        $this->makeRequest([
            'audio' => UploadedFile::fake()->create('updated.mp3'),
        ])->assertSuccessful();

        $this->assertNotEquals('updated.mp3', $this->track->audio->fullName());
    }

    #[Test]
    public function when_modifying_a_track_it_can_modify_genres()
    {
        $this->track->update(['genres' => null]);

        $this->makeRequest([
            'genres' => [Genres::HIP_HOP, Genres::HARDCORE],
        ])->assertSuccessful();

        $this->assertCount(2, $this->track->fresh()->genres);
    }

    #[Test]
    public function when_modifying_a_track_it_can_modify_tags()
    {
        $this->track->forceFill([
            'tags' => null,
        ])->saveQuietly();

        $this->makeRequest([
            'tags' => ['test_1', 'test_2'],
        ])->assertSuccessful();

        $this->assertCount(2, $this->track->fresh()->tags);
    }

    #[Test]
    public function it_wont_let_update_tracks_from_other_user()
    {
        $otherUser = create(User::class);
        $this->track->created_by = $otherUser->id;
        $this->track->save();

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_update_tracks_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    //    /** @test */
    //    public function it_wont_let_update_tracks_if_its_in_battle()
    //    {
    //        /** @var Battle $battle */
    //        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS]);
    //        $this->track->battles()->save($battle);
    //
    //        $this->makeRequest()
    //            ->assertForbidden();
    //    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('tracks.update', $this->track), array_merge(['name' => 'edited'], $attributes));
    }
}
