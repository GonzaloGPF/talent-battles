<?php

namespace Tests\Feature\Models;

use App\Models\Activity;
use App\Models\Album;
use App\Models\Cart;
use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\Invitation;
use App\Models\Like;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Report;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(User::class);
    }

    #[Test]
    public function user_has_many_creations()
    {
        $this->assertHasMany($this->model, Track::class);
    }

    #[Test]
    public function user_morph_many_likes()
    {
        $this->assertMorphsMany($this->model, Like::class);
    }

    #[Test]
    public function user_morph_many_favorites()
    {
        $this->assertMorphsMany($this->model, Favorite::class);
    }

    #[Test]
    public function user_has_many_albums()
    {
        $this->assertHasMany($this->model, Album::class);
    }

    #[Test]
    public function user_has_many_orders()
    {
        $this->assertHasMany($this->model, Order::class);
    }

    #[Test]
    public function user_has_many_order_lines_through_orders()
    {
        $this->assertHasManyThrough($this->model, OrderLine::class);
    }

    #[Test]
    public function user_has_many_activities()
    {
        $this->assertHasMany($this->model, Activity::class);
    }

    #[Test]
    public function user_has_many_feed()
    {
        $this->assertHasMany($this->model, Activity::class, 'feed');
    }

    #[Test]
    public function user_morphs_many_reports()
    {
        $this->assertMorphsMany($this->model, Report::class);
    }

    #[Test]
    public function user_has_many_created_reports()
    {
        $this->assertHasMany($this->model, Report::class, 'createdReports');
    }

    #[Test]
    public function user_has_many_favorite_lists()
    {
        $this->assertHasMany($this->model, FavoriteList::class);
    }

    #[Test]
    public function user_has_many_invitations()
    {
        $this->assertHasMany($this->model, Invitation::class);
    }

    #[Test]
    public function user_has_one_chart()
    {
        $this->assertHasOne($this->model, Cart::class);
    }

    #[Test]
    public function it_users_broadcast_events_trait()
    {
        $this->assertUsesTrait($this->model, BroadcastsEvents::class);
    }
}
