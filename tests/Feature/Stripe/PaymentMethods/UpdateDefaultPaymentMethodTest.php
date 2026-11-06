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

class UpdateDefaultPaymentMethodTest extends TestCase
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
    public function it_can_set_default_payment_method()
    {
        $paymentMethods = collect([
            ['id' => 'SomeId'],
        ]);
        $this->mock(StripeApi::class, function (MockInterface $mock) use ($paymentMethods) {
            $mock->shouldReceive('getPaymentMethods')
                ->once()
                ->andReturn($paymentMethods);

            $mock->shouldReceive('setDefaultPaymentMethod')
                ->once()
                ->withArgs(['SomeId'])
                ->andReturn($this->createStub(PaymentMethod::class));
        });

        $this->makeRequest(['id' => 'SomeId'])
            ->assertSuccessful();
    }

    #[Test]
    public function it_wont_let_set_default_payment_method_if_its_a_wrong_id()
    {
        $paymentMethods = collect([
            ['id' => 'SomeId'],
        ]);
        $this->mock(StripeApi::class, function (MockInterface $mock) use ($paymentMethods) {
            $mock->shouldReceive('getPaymentMethods')
                ->once()
                ->andReturn($paymentMethods);
        });

        $this->makeRequest(['id' => 'WrongId'])
            ->assertForbidden();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('stripe.default_payment_methods.update', $attributes));
    }
}
