<?php

namespace Tests\Feature\Albums;

use App\Enums\Roles;
use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteAlbumTest extends TestCase
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
    public function it_let_delete_album()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertDatabaseMissing('albums', ['id' => $this->album->id]);
    }

    #[Test]
    public function it_wont_let_users_delete_foreign_albums()
    {
        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $otherUser = create(User::class);
        $this->album->created_by = $otherUser->id;
        $this->album->save();

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('albums.destroy', $this->album));
    }
}
