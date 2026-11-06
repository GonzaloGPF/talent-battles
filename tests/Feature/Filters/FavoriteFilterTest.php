<?php

namespace Tests\Feature\Filters;

use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = create(Track::class);
        create(Favorite::class, [
            'created_by' => create(User::class)->id,
            'favoritable_type' => Track::class,
            'favoritable_id' => $this->model->id,
        ]);
    }

    #[Test]
    public function it_can_filter_favorites_by_created_by()
    {
        /** @var User $user */
        $user = create(User::class);
        create(Favorite::class, [
            'created_by' => $user->id,
        ]);

        $data = Favorite::filter(['created_by' => $user->id])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_favorites_by_favorite_list()
    {
        $this->signIn($user = $this->signIn());

        Favorite::query()->update([
            'created_by' => $user->id,
            'favorite_list_id' => null,
            'favoritable_id' => 0,
            'favoritable_type' => 'someType',
        ]);

        /** @var FavoriteList $favoriteList */
        $favoriteList = create(FavoriteList::class, ['created_by' => $user->id]);
        create(Favorite::class, [
            'created_by' => $user->id,
            'favorite_list_id' => $favoriteList->id,
        ]);

        $data = Favorite::filter(['favorite_list' => [$favoriteList->id]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_favorites_by_type()
    {
        Favorite::query()->update(['favoritable_type' => User::class]);

        /** @var Track $track */
        $track = create(Track::class);
        create(Favorite::class, [
            'favoritable_id' => $track->id,
            'favoritable_type' => Track::class,
        ]);

        $data = Favorite::filter(['type' => ['track']])->get();

        $this->assertCount(1, $data);
    }
}
