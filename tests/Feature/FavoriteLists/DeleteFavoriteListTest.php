<?php

namespace Tests\Feature\FavoriteLists;

use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteFavoriteListTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FavoriteList $favoriteList;

    private Favorite $favorite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->favoriteList = create(FavoriteList::class);
        $this->favorite = create(Favorite::class, ['favorite_list_id' => $this->favoriteList->id]);
    }

    #[Test]
    public function it_let_delete_a_favorite_list()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertNull($this->favorite->fresh()->favorite_list_id);
        $this->assertDatabaseMissing('favorite_lists', ['id' => $this->favoriteList->id]);
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('favorite_lists.destroy', $this->favoriteList));
    }
}
