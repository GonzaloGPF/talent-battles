<?php

namespace Tests\Feature\Services;

use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    private TagService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(TagService::class);
    }

    #[Test]
    public function it_can_create_new_tags()
    {
        $this->service->saveTags(['foo', 'bar']);

        $tags = Tag::query()->orderBy('id')->get();

        /** @var Tag $first */
        $first = $tags->first();
        $this->assertEquals('foo', $first->name);

        /** @var Tag $last */
        $last = $tags->last();
        $this->assertEquals('bar', $last->name);
    }

    #[Test]
    public function it_wont_create_new_tag_if_already_exists()
    {
        create(Tag::class, ['name' => 'Test']);

        $this->service->saveTags(['test']);

        $this->assertEquals(1, Tag::count());
    }

    #[Test]
    public function when_creating_tags_it_will_ignore_duplicates()
    {
        $this->service->saveTags(['test', 'other', 'test']);

        $this->assertEquals(2, Tag::count());
    }
}
