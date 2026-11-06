<?php

namespace Tests\Feature\FavoriteLists;

use App\Models\FavoriteList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewFavoriteListTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FavoriteList $favoriteList;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->favoriteList = create(FavoriteList::class);

        create(FavoriteList::class, ['created_by' => create(User::class)->id]); // extra favorite list from foreign user
    }

    #[Test]
    public function it_let_user_view_favorite_lists()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('favorite_lists.index'));
    }
}
