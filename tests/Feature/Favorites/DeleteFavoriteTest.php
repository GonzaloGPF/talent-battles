<?php

namespace Tests\Feature\Favorites;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteFavoriteTest extends TestCase
{
    use RefreshDatabase;

    private Favorite $favorite;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->favorite = create(Favorite::class, [
            'created_by' => $this->user->id,
            'favoritable_type' => 'SomeType',
            'favoritable_id' => 0,
        ]);
    }

    #[Test]
    public function it_let_remove_a_favorite()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(0, $this->user->favorites);
    }

    #[Test]
    public function it_wont_let_remove_a_favorite_of_other_user()
    {
        $this->favorite->created_by = create(User::class)->id;
        $this->favorite->save();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_remove_a_favorite_if_non_authenticated_users()
    {
        Auth::logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('favorites.destroy', $this->favorite));
    }
}
