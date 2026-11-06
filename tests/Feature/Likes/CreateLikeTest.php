<?php

namespace Tests\Feature\Likes;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\Like;
use App\Models\Track;
use App\Models\User;
use App\Notifications\LikeReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateLikeTest extends TestCase
{
    use RefreshDatabase;

    private Track $model;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->user = $this->signIn();
        $this->model = create(Track::class, [
            'created_by' => create(User::class)->id,
        ]);
    }

    #[Test]
    public function it_let_create_a_like_for_a_likeable_model()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(1, $this->model->likes);
    }

    #[Test]
    public function when_creating_a_like_it_will_save_an_activity_too()
    {
        $this->makeRequest()
            ->assertSuccessful();

        /** @var Activity $activity */
        $activity = Activity::first();

        $this->assertEquals(ActivityTypes::DO_LIKE, $activity->activity_type);
        $this->assertEquals($this->user->id, $activity->created_by);
        $this->assertEquals($this->model->created_by, $activity->receiver_id);
        $this->assertEquals($this->model->id, $activity->target_id);
        $this->assertEquals(Track::class, $activity->target_type);
    }

    #[Test]
    public function when_creating_a_like_it_will_send_a_notification()
    {
        Notification::fake();

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $like = Like::find($data['id']);

        Notification::assertSentTo($this->model->createdBy, function (LikeReceived $notification) use ($like) {
            return $notification->like->id === $like->id;
        });
    }

    #[Test]
    public function it_wont_let_like_same_model_twice()
    {
        create(Like::class, [
            'created_by' => $this->user->id,
            'likeable_type' => Track::class,
            'likeable_id' => $this->model->id,
        ]);

        $this->makeRequest()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Test]
    public function it_wont_let_like_a_model_created_by_logged_user()
    {
        $this->model->created_by = $this->user->id;
        $this->model->save();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_like_non_authenticated_users()
    {
        Auth::logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(): TestResponse
    {
        return $this->postJson(route('likes.store', [
            'model_type' => 'track',
            'model_id' => $this->model->id,
        ]));
    }
}
