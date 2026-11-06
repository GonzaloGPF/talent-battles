<?php

namespace Tests\Feature\Albums;

use App\Enums\Roles;
use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateAlbumTest extends TestCase
{
    use RefreshDatabase;

    private Album $album;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->album = create(Album::class);
    }

    #[Test]
    public function it_let_update_album()
    {
        $this->makeRequest(['name' => 'Test'])
            ->assertSuccessful();

        $this->assertEquals('Test', $this->album->fresh()->name);
    }

    #[Test]
    public function it_let_users_change_album_image()
    {
        $this->makeRequest([
            'image' => UploadedFile::fake()->image('edited.webp'),
        ])->assertSuccessful();

        $this->assertEquals('edited', $this->album->image->name);
    }

    #[Test]
    public function it_wont_let_users_update_foreign_albums()
    {
        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $otherUser = create(User::class);
        $this->album->created_by = $otherUser->id;
        $this->album->save();

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('albums.update', $this->album), $attributes);
    }
}
