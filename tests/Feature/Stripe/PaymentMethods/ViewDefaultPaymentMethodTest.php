<?php

namespace Tests\Feature\Stripe\PaymentMethods;

use App\Api\StripeApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Cashier\PaymentMethod;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewDefaultPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class, ['stripe_id' => 'SomeId']);
        $this->signIn($this->user);
    }

    #[Test]
    public function it_can_view_default_payment_method_of_logged_user()
    {
        $paymentMethod = $this->createStub(PaymentMethod::class);

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($paymentMethod) {
            $mock->shouldReceive('getDefaultPaymentMethod')
                ->once()
                ->andReturn($paymentMethod);
        });

        $this->makeRequest()
            ->assertSuccessful();
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('stripe.default_payment_methods.show'));
    }
}
