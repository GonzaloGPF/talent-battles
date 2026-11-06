<?php

namespace Tests\Feature\Stripe\Accounts;

use App\Api\StripeApi;
use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
    }

    #[Test]
    public function admin_can_view_all_system_accounts()
    {
        $response = json_decode(mockFileContent('responses/stripe/accounts.json'), true);

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($response) {
            $mock->shouldReceive('getAccounts')
                ->once()
                ->andReturn(collect($response['data']));
        });

        $this->getJson(route('stripe.accounts.index'))
            ->assertSuccessful();
    }

    #[Test]
    public function non_admin_cannot_view_all_system_accounts()
    {
        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->getJson(route('stripe.accounts.index'))
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_view_payment_methods_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('stripe.accounts.index'));
    }
}
