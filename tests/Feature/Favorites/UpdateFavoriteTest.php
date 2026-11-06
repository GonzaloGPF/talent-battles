<?php

namespace Tests\Feature\Favorites;

use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateFavoriteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Favorite $favorite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->favorite = create(Favorite::class, [
            'created_by' => $this->user->id,
            'favorite_list_id' => null,
        ]);
    }

    #[Test]
    public function it_let_update_a_favorite_by_modifying_its_favorite_list()
    {
        $this->assertNull($this->favorite->favorite_list_id);

        /** @var FavoriteList $favoriteList */
        $favoriteList = create(FavoriteList::class, [
            'created_by' => $this->user->id,
            'name' => 'someName',
        ]);

        $this->makeRequest(['favorite_list_id' => $favoriteList->id])
            ->assertSuccessful();

        $this->assertEquals($favoriteList->id, $this->favorite->fresh()->favorite_list_id);
    }

    #[Test]
    public function it_wont_let_assign_a_favorite_list_id_if_selected_list_id_belongs_to_another_user()
    {
        $this->assertNull($this->favorite->favorite_list_id);

        /** @var FavoriteList $favoriteList */
        $favoriteList = create(FavoriteList::class, ['created_by' => create(User::class)->id]);

        $this->makeRequest(['favorite_list_id' => $favoriteList->id])
            ->assertUnprocessable();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('favorites.update', $this->favorite), $attributes);
    }
}
