<?php

namespace Tests\Feature\Stripe\Accounts;

use App\Api\StripeApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateAccountLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn(create(User::class, ['account' => ['id' => 'SomeId']]));
    }

    #[Test]
    public function it_let_create_an_account_link()
    {
        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('createAccountLink')
                ->once();
        });

        $this->makeRequest()
            ->assertSuccessful();
    }

    private function makeRequest(): TestResponse
    {
        return $this->postJson(route('stripe.accounts.links.store'));
    }
}
