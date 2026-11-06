<?php

namespace Tests\Feature\Albums;

use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateAlbumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
    }

    #[Test]
    public function it_let_create_albums_correctly()
    {
        $this->makeRequest([
            'name' => 'test',
            'description' => 'Some description',
        ])->assertSuccessful();

        $album = Album::first();

        $this->assertEquals('Test', $album->name);
        $this->assertEquals('Some description', $album->description);
    }

    #[Test]
    public function when_creating_an_album_it_can_specify_tags()
    {
        $this->makeRequest([
            'name' => 'test',
            'description' => 'Some description',
            'tags' => ['some', 'tag', 'test'],
        ])->assertSuccessful();

        $album = Album::first();

        $this->assertCount(3, $album->tags);
    }

    #[Test]
    public function when_creating_an_album_user_can_specify_an_image()
    {
        $this->makeRequest([
            'name' => 'test',
            'description' => 'Some description',
            'image' => UploadedFile::fake()->image('my_image.webp'),
        ])->assertSuccessful();

        $album = Album::first();

        $this->assertEquals('my_image', $album->image->name);
    }

    #[Test]
    public function it_wont_let_create_albums_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('albums.store'), $attributes);
    }
}
