<?php

namespace Tests\Feature\Filters;

use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AlbumFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        create(Album::class, [], 3);
    }

    #[Test]
    public function it_can_filter_albums_by_description()
    {
        Album::query()->update(['description' => null]);

        create(Album::class, ['description' => 'Test']);

        $data = Album::filter(['description' => 'test'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_creations_by_tags()
    {
        Album::query()->update(['tags' => null]);

        create(Album::class, ['tags' => ['test']]);

        $data = Album::filter(['tags' => ['test']])->get();

        $this->assertCount(1, $data);
    }
}
