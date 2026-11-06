<?php

namespace Tests\Feature\Comments;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Track;
use App\Models\User;
use App\Notifications\CommentReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Queue;
use Tests\TestCase;

class CreateCommentTest extends TestCase
{
    use RefreshDatabase;

    private Track $track;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->user = $this->signIn();
        $this->track = create(Track::class, ['created_by' => $this->user->id]);
    }

    #[Test]
    public function authenticated_user_can_create_comments()
    {
        $this->makeRequest(['content' => 'Some text'])
            ->assertSuccessful();

        $this->assertCount(1, $this->track->comments);

        $comment = Comment::first();
        $this->assertEquals(auth()->id(), $comment->created_by);
        $this->assertEquals('Some text', $comment->content);
    }

    #[Test]
    public function authenticated_user_can_create_a_comment_with_files()
    {
        $this->makeRequest([
            'files' => [
                UploadedFile::fake()->create('test_1.txt'),
                UploadedFile::fake()->create('test_2.txt'),
            ],
        ])->assertSuccessful();

        $this->assertCount(2, Comment::first()->files);
    }

    #[Test]
    public function authenticated_user_can_create_a_comment_to_another_comment()
    {
        /** @var Comment $otherComment */
        $otherComment = create(Comment::class, [
            'created_by' => create(User::class)->id,
        ]);
        $newCommentId = $this->makeRequest([
            'content' => 'Some content',
            'model_type' => 'comment',
            'model_id' => $otherComment->id,
            'comment_id' => $otherComment->id,
        ])->assertSuccessful()
            ->json('data.id');

        $newComment = Comment::find($newCommentId);
        $this->assertEquals('Some content', $newComment->content);
        $this->assertEquals($otherComment->id, $newComment->comment_id);
    }

    #[Test]
    public function when_creating_a_comment_it_will_save_an_activity_too()
    {
        $this->makeRequest()
            ->assertSuccessful();

        /** @var Activity $activity */
        $activity = Activity::first();

        $this->assertEquals(ActivityTypes::DO_COMMENT, $activity->activity_type);
        $this->assertEquals($this->user->id, $activity->created_by);
        $this->assertEquals($this->track->created_by, $activity->receiver_id);
        $this->assertEquals($this->track->id, $activity->target_id);
        $this->assertEquals(Track::class, $activity->target_type);
    }

    #[Test]
    public function when_creating_a_comment_it_will_send_a_notification()
    {
        Notification::fake();

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $comment = Comment::find($data['id']);

        Notification::assertSentTo($this->track->createdBy, function (CommentReceived $notification) use ($comment) {
            return $notification->comment->id === $comment->id;
        });
    }

    #[Test]
    public function it_wont_let_comment_non_authenticated_user()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('comments.store'), array_merge([
            'content' => 'Some content',
            'model_type' => 'track',
            'model_id' => $this->track->id,
        ], $attributes));
    }
}
