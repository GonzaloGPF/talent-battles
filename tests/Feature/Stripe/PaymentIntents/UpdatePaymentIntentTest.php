<?php

namespace Stripe\PaymentIntents;

use App\Api\StripeApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Stripe\PaymentIntent;
use Tests\TestCase;

class UpdatePaymentIntentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_let_updates_a_payment_intent()
    {
        $mockedPaymentIntent = new PaymentIntent;
        $mockedPaymentIntent->client_secret = 'some_secret';

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($mockedPaymentIntent) {
            $mock->shouldReceive('retrievePaymentIntent')
                ->once()
                ->with('someId')
                ->andReturn($mockedPaymentIntent);

            $mock->shouldReceive('updatePaymentIntent')
                ->once()
                ->with($mockedPaymentIntent, 123)
                ->andReturn($mockedPaymentIntent);
        });

        $this->makeRequest($mockedPaymentIntent, 123)
            ->assertSuccessful();
    }

    private function makeRequest(PaymentIntent $paymentIntent, float $amount): TestResponse
    {
        return $this->putJson(route('stripe.payment_intents.update', 'someId'), [
            'amount' => $amount,
        ]);
    }
}
