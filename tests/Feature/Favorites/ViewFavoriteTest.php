<?php

namespace Tests\Feature\Favorites;

use App\Models\Favorite;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewFavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_let_view_favorites()
    {
        create(Favorite::class, [
            'favoritable_id' => 0,
            'favoritable_type' => 'SomeType',
        ]);

        $data = $this->getJson(route('favorites.index'))
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_let_view_an_specific_favorite()
    {
        $user = $this->signIn();

        /** @var Track $track */
        $track = create(Track::class, ['created_by' => create(User::class)->id]);

        /** @var Favorite $favorite */
        $favorite = create(Favorite::class, [
            'created_by' => $user->id,
            'favoritable_id' => $track->id,
            'favoritable_type' => Track::class,
        ]);

        $data = $this->getJson(route('favorites.show', [
            'favorite' => $favorite->id,
            'with' => ['favoritable'],
        ]))
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($favorite->id, $data['id']);
    }

    #[Test]
    public function when_viewing_an_specific_favorite_it_wont_let_view_it_if_it_belong_to_another_user()
    {
        $this->signIn();

        /** @var Track $track */
        $track = create(Track::class, ['created_by' => create(User::class)->id]);

        /** @var Favorite $favorite */
        $favorite = create(Favorite::class, [
            'created_by' => create(User::class)->id,
            'favoritable_id' => $track->id,
            'favoritable_type' => Track::class,
        ]);

        $this->getJson(route('favorites.show', [
            'favorite' => $favorite->id,
            'with' => ['favoritable'],
        ]))->assertForbidden();
    }
}
