<?php

namespace Tests\Feature\Favorites;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\Track;
use App\Models\User;
use App\Notifications\FavoriteReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateFavoriteTest extends TestCase
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
    public function it_let_create_a_favorite_for_a_favoritable_model()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(1, $this->model->favorites);
    }

    #[Test]
    public function when_creating_a_favorite_for_a_favoritable_model_user_can_specify_a_favorite_list()
    {
        /** @var FavoriteList $favoriteList */
        $favoriteList = create(FavoriteList::class, ['created_by' => $this->user->id]);
        $this->makeRequest([
            'favorite_list_id' => $favoriteList->id,
        ])
            ->assertSuccessful();

        $favorite = Favorite::first();
        $this->assertEquals($this->user->id, $favorite->created_by);
        $this->assertEquals($favoriteList->id, $favorite->favorite_list_id);
    }

    #[Test]
    public function when_creating_a_favorite_for_a_favoritable_model_user_can_specify_create_new_favorite_list()
    {
        $this->makeRequest([
            'favorite_list_id' => 'test',
        ])
            ->assertSuccessful();

        $favorite = Favorite::first();
        $favoriteList = FavoriteList::first();
        $this->assertEquals($this->user->id, $favorite->created_by);
        $this->assertEquals($favoriteList->id, $favorite->favorite_list_id);
        $this->assertEquals('Test', $favoriteList->name);
    }

    #[Test]
    public function when_creating_a_favorite_for_a_favoritable_model_user_cannot_specify_a_favorite_list_that_belongs_to_other_user()
    {
        /** @var FavoriteList $favoriteList */
        $favoriteList = create(FavoriteList::class, ['created_by' => create(User::class)->id]);
        $this->makeRequest([
            'favorite_list_id' => $favoriteList->id,
        ])
            ->assertSuccessful();

        $favorite = Favorite::first();
        $this->assertNull($favorite->favorite_list_id);
    }

    #[Test]
    public function when_creating_a_favorite_it_will_save_an_activity_too()
    {
        $this->makeRequest()
            ->assertSuccessful();

        /** @var Activity $activity */
        $activity = Activity::first();

        $this->assertEquals(ActivityTypes::DO_FAVORITE, $activity->activity_type);
        $this->assertEquals($this->user->id, $activity->created_by);
        $this->assertEquals($this->model->created_by, $activity->receiver_id);
        $this->assertEquals($this->model->id, $activity->target_id);
        $this->assertEquals(Track::class, $activity->target_type);
    }

    #[Test]
    public function when_creating_a_favorite_it_will_send_a_notification()
    {
        Notification::fake();

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $favorite = Favorite::find($data['id']);

        Notification::assertSentTo($this->model->createdBy, function (FavoriteReceived $notification) use ($favorite) {
            return $notification->favorite->id === $favorite->id;
        });
    }

    #[Test]
    public function it_wont_let_favorite_same_model_twice()
    {
        create(Favorite::class, [
            'created_by' => $this->user->id,
            'favoritable_type' => Track::class,
            'favoritable_id' => $this->model->id,
        ]);

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_favorite_a_model_created_by_logged_user()
    {
        $this->model->created_by = $this->user->id;
        $this->model->save();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_favorite_non_authenticated_users()
    {
        Auth::logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('favorites.store', array_merge([
            'model_type' => 'track',
            'model_id' => $this->model->id,
        ], $attributes)));
    }
}
