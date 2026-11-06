<?php

namespace Tests\Feature\Auth;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class);
    }

    #[Test]
    public function it_lets_users_log_in()
    {
        $data = $this->loginRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertArrayNotHasKey('token', $data);
    }

    #[Test]
    public function it_shows_token_data_when_login_from_external_app()
    {
        $data = $this->loginRequest([])
            ->assertSuccessful()
            ->json('data');

        $this->assertArrayHasKey('token', $data);
    }

    #[Test]
    public function it_let_users_logout()
    {
        $this->signIn();

        $this->postJson(route('auth.logout'))
            ->assertSuccessful();
    }

    #[Test]
    public function when_user_login_it_will_sync_his_cart_if_created_it_as_guest()
    {
        /** @var Cart $cart */
        $cart = create(Cart::class, [
            'created_by' => null,
            'guest_id' => 'someId',
        ]);

        session()->put('cart_session_key', 'someId');

        $this->loginRequest()
            ->assertSuccessful();

        $this->assertEquals($this->user->id, $cart->fresh()->created_by);
    }

    /**
     * @param  null  $headers
     */
    private function loginRequest($headers = null): TestResponse
    {
        $headers = $headers ?? ['X-Requested-With' => 'XMLHttpRequest'];

        return $this->postJson(route('auth.login'), [
            'email' => $this->user->email,
            'password' => 'Secret123**',
        ], $headers);
    }
}
