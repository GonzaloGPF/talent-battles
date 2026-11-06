<?php

namespace Tests\Feature\Likes;

use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewLikeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_let_view_likes()
    {
        create(Like::class, [
            'likeable_id' => 0,
            'likeable_type' => 'SomeType',
        ]);

        $data = $this->getJson(route('likes.index'))
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }
}
