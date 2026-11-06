<?php

namespace Tests\Feature\Users;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
    }

    #[Test]
    public function administrator_can_delete_users()
    {
        /** @var User $user */
        $user = create(User::class);

        $this->assertNull($user->deleted_at);

        $this->makeRequest($user)
            ->assertSuccessful();

        $this->assertNotNull($user->fresh()->deleted_at);
    }

    #[Test]
    public function it_wont_let_user_delete_other_user()
    {
        $this->signIn(create(User::class, ['role' => Roles::USER]));

        /** @var User $user */
        $user = create(User::class);

        $this->makeRequest($user)
            ->assertForbidden();
    }

    #[Test]
    public function it_let_users_delete_himself()
    {
        $user = $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest($user)
            ->assertSuccessful();

        $this->assertNotNull($user->fresh()->deleted_at);
    }

    private function makeRequest(User $user): TestResponse
    {
        return $this->deleteJson(route('users.destroy', $user));
    }
}
