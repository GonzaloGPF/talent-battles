<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserVerifyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class, ['token' => 'SomeToken']);
    }

    #[Test]
    public function it_lets_users_verify_its_email()
    {
        $this->makeRequest()
            ->assertSuccessful();

        tap($this->user->fresh(), function (User $user) {
            $this->assertNotEmpty($user->verified_at);
            $this->assertEmpty($user->token);
        });
    }

    private function makeRequest(): TestResponse
    {
        return $this->get(route('users.verify', ['user' => $this->user->token]));
    }
}
