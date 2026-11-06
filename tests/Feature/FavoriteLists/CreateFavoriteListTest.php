<?php

namespace Tests\Feature\FavoriteLists;

use App\Models\FavoriteList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateFavoriteListTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
    }

    #[Test]
    public function it_let_delete_a_favorite_list()
    {
        $this->makeRequest(['name' => 'test'])
            ->assertSuccessful();

        $favoriteList = FavoriteList::first();
        $this->assertEquals('Test', $favoriteList->name);
        $this->assertEquals($this->user->id, $favoriteList->created_by);
    }

    #[Test]
    public function it_wont_let_a_user_create_favorite_list_with_same_name_of_other_of_their_favorite_lists()
    {
        create(FavoriteList::class, [
            'name' => 'Same',
            'created_by' => $this->user->id,
        ]);

        $this->makeRequest(['name' => 'Same'])
            ->assertUnprocessable();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('favorite_lists.store'), $attributes);
    }
}
