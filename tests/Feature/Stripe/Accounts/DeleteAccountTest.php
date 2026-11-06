<?php

namespace Tests\Feature\Stripe\Accounts;

use App\Api\StripeApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn(create(User::class, ['account' => ['id' => 'SomeId']]));
    }

    #[Test]
    public function it_can_delete_an_account_from_logged_user()
    {
        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('destroyAccount')
                ->once()
                ->andReturn([
                    'id' => 'SomeId',
                    'object' => 'account',
                    'deleted' => true,
                ]);
        });

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertNull($this->user->fresh()->account);
    }

    #[Test]
    public function it_wont_let_delete_accounts_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('stripe.accounts.destroy', $this->user));
    }
}
