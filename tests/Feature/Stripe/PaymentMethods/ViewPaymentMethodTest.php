<?php

namespace Tests\Feature\Stripe\PaymentMethods;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
    }

    #[Test]
    public function it_can_view_payment_methods()
    {
        $this->makeRequest()
            ->assertSuccessful();
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
        return $this->getJson(route('stripe.payment_methods.index'));
    }
}
