<?php

namespace Tests\Feature\Stripe\PaymentMethods;

use App\Api\StripeApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Cashier\Exceptions\InvalidPaymentMethod;
use Laravel\Cashier\PaymentMethod;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreatePaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class, ['stripe_id' => 'SomeId']);
        $this->signIn($this->user);
    }

    /**
     * @throws InvalidPaymentMethod
     */
    #[Test]
    public function it_can_create_payment_methods()
    {
        $paymentMethod = $this->createStub(PaymentMethod::class);

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($paymentMethod) {
            $mock->shouldReceive('createOrGetStripeCustomer')
                ->once();
            //                ->withArgs([$user]);
            $mock->shouldReceive('addPaymentMethod')
                ->once()
                ->with('Some String', $this->user)
                ->andReturn($paymentMethod);
            $mock->shouldReceive('getPaymentMethods')
                ->once()
                ->withNoArgs()
                ->andReturn(collect());
            $mock->shouldReceive('setDefaultPaymentMethod')
                ->once()
                ->withArgs([$paymentMethod->id])
                ->andReturn($paymentMethod);
        });

        $this->makeRequest(['payment_method_id' => 'Some String'])
            ->assertSuccessful();
    }

    #[Test]
    public function it_wont_let_create_payment_methods_to_non_authenticated_users()
    {
        User::flushEventListeners();
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('stripe.payment_methods.store'), array_merge([
            'payment_method_id' => 'Some String',
        ], $attributes));
    }
}
