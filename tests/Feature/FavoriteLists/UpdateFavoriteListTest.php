<?php

namespace Tests\Feature\FavoriteLists;

use App\Models\FavoriteList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateFavoriteListTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteList $favoriteList;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->favoriteList = create(FavoriteList::class);
    }

    #[Test]
    public function it_let_update_a_favorite_list()
    {
        $this->makeRequest(['name' => 'test'])
            ->assertSuccessful();

        $this->assertEquals('Test', $this->favoriteList->fresh()->name);
    }

    private function makeRequest(array $attributes): TestResponse
    {
        return $this->putJson(route('favorite_lists.update', $this->favoriteList), $attributes);
    }
}
