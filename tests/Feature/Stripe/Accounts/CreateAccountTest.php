<?php

namespace Tests\Feature\Stripe\Accounts;

use App\Api\StripeApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Account;
use Tests\TestCase;

class CreateAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
    }

    #[Test]
    public function authenticated_user_can_create_an_account_from_stripe()
    {
        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('createAccount')
                ->once()
                ->andReturn(new Account(['id' => 'SomeId']));
        })->makePartial();

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals('SomeId', auth()->user()->account['id']);
    }

    #[Test]
    public function when_activating_an_account_from_stripe_if_user_already_has_account_it_will_fetch_it()
    {
        $this->user
            ->forceFill(['account' => ['id' => 'SomeId']])
            ->save();

        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAccount')
                ->once()
                ->andReturn(new Account(['id' => 'SomeId']));
        })->makePartial();

        $this->makeRequest()
            ->assertSuccessful();
    }

    #[Test]
    public function when_activating_an_account_from_stripe_and_it_produces_error_it_will_return_an_empty_response()
    {
        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('createAccount')
                ->once()
                ->andReturn(null);
        })->makePartial();

        $this->makeRequest()
            ->assertNoContent();
    }

    #[Test]
    public function it_wont_let_create_an_account_if_not_authenticated()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(): TestResponse
    {
        return $this->postJson(route('stripe.accounts.store'));
    }
}
