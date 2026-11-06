<?php

namespace Stripe\PaymentIntents;

use App\Api\StripeApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Cashier\Payment;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Stripe\PaymentIntent;
use Tests\TestCase;

class CreatePaymentIntentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_let_non_authenticated_users_to_create_payment_intents()
    {
        $paymentIntentMock = new PaymentIntent;
        $paymentIntentMock->client_secret = 'some_secret';
        $payment = new Payment($paymentIntentMock);

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($payment) {
            $mock->shouldReceive('createPaymentIntent')
                ->once()
                ->with(123, 'some@email.com')
                ->andReturn($payment);
        });

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals('some_secret', $data['client_secret']);
    }

    #[Test]
    public function it_let_authenticated_users_to_create_payment_intents()
    {
        $this->signIn();

        $paymentIntentMock = new PaymentIntent;
        $paymentIntentMock->client_secret = 'some_secret';
        $payment = new Payment($paymentIntentMock);

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($payment) {
            $mock->shouldReceive('createPaymentIntent')
                ->once()
                ->with(123, 'some@email.com')
                ->andReturn($payment);
        });

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals('some_secret', $data['client_secret']);
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('stripe.payment_intents.store', array_merge([
            'amount' => 123,
            'email' => 'some@email.com',
        ], $attributes)));
    }
}
