<?php

namespace Tests\Feature\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewTagTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Tag::class, [], 3);
    }

    #[Test]
    public function it_can_fetch_tags()
    {
        $this->makeRequest()
            ->assertSuccessful();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->getJson(route('tags.index', $attributes));
    }
}
