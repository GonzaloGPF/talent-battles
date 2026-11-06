<?php

namespace Tests\Feature\Albums;

use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewAlbumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Album::class, [], 2);
    }

    #[Test]
    public function it_let_view_albums()
    {
        $this->getJson(route('albums.index'))
            ->assertSuccessful();
    }

    #[Test]
    public function it_can_view_an_specific_album()
    {
        $this->getJson(route('albums.show', Album::first()))
            ->assertSuccessful();
    }
}
