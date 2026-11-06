<?php

namespace Tests\Feature\Comments;

use App\Enums\Roles;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteCommentTest extends TestCase
{
    use RefreshDatabase;

    private Comment $comment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->comment = create(Comment::class);
    }

    #[Test]
    public function authenticated_user_can_delete_an_comment()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertDatabaseMissing('comments', ['id' => $this->comment->id]);
    }

    #[Test]
    public function user_cannot_delete_comments_from_other_user()
    {
        $otherUser = create(User::class);
        $otherComment = create(Comment::class, ['created_by' => $otherUser->id]);

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest($otherComment)
            ->assertForbidden();
    }

    /**
     * @param  null  $comment
     */
    private function makeRequest($comment = null): TestResponse
    {
        return $this->deleteJson(route('comments.destroy', $comment ?? $this->comment));
    }
}
