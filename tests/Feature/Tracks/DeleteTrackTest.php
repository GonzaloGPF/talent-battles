<?php

namespace Tests\Feature\Tracks;

use App\Enums\BattleStates;
use App\Enums\FileTypes;
use App\Enums\Roles;
use App\Models\Battle;
use App\Models\File;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteTrackTest extends TestCase
{
    use RefreshDatabase;

    private Track $track;

    private File $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->track = create(Track::class);
        $this->file = make(File::class, [
            'fileable_id' => $this->track,
            'fileable_type' => Track::class,
            'file_type' => FileTypes::AUDIO,
            'mime' => 'audio/mpeg',
            'path' => generateTestFile('test', 'mp3'),
            'name' => 'test',
        ]);
        $this->file->saveQuietly();
    }

    #[Test]
    public function it_let_users_delete_its_track()
    {
        $this->assertTrue(Storage::exists($this->file->path));

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertDatabaseMissing('tracks', ['id' => $this->track->id]);
        $this->assertFalse(Storage::exists($this->file->path));
    }

    #[Test]
    public function it_wont_let_delete_tracks_from_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        $this->track->created_by = $otherUser->id;
        $this->track->save();

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_delete_tracks_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    #[Test]
    public function it_wont_let_delete_tracks_if_its_in_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS]);
        $this->track->battles()->save($battle);

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('tracks.destroy', $this->track));
    }
}
