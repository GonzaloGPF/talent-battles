<?php

namespace Tests\Feature\Likes;

use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteLikeTest extends TestCase
{
    use RefreshDatabase;

    private Like $like;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->like = create(Like::class, [
            'created_by' => $this->user->id,
            'likeable_type' => 'SomeType',
            'likeable_id' => 0,
        ]);
    }

    #[Test]
    public function it_let_remove_a_like()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(0, $this->user->likes);
    }

    #[Test]
    public function it_wont_let_remove_a_like_of_other_user()
    {
        $this->like->created_by = create(User::class)->id;
        $this->like->save();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_remove_a_like_if_non_authenticated_users()
    {
        Auth::logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('likes.destroy', $this->like));
    }
}
