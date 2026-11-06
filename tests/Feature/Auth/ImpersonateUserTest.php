<?php

namespace Tests\Feature\Auth;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImpersonateUserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class);
    }

    #[Test]
    public function administrator_can_impersonate_users()
    {
        $this->signIn();

        $this->assertNotEquals($this->user->id, auth()->id());

        $response = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        //        $this->assertEquals($user->id, auth()->id());
        $this->assertNotEquals(hash('sha256', $this->user->tokens->first()->token), $response['token']);
    }

    #[Test]
    public function non_administrators_cannot_impersonate_users()
    {
        $this->signIn(create(User::class, ['role' => Roles::USER]));
        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(): TestResponse
    {
        return $this->postJson(route('impersonate', $this->user));
    }
}
