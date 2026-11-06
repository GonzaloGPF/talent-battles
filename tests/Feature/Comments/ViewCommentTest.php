<?php

namespace Tests\Feature\Comments;

use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewCommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Comment::class, [], 3); // extra Comments
    }

    #[Test]
    public function authenticated_user_can_fetch_all_comments()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(Comment::count(), $data);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('comments.index'));
    }
}
