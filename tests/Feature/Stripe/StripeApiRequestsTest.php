<?php

namespace Tests\Feature\Stripe;

use App\Api\StripeApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\Util\ApiVersion;
use Tests\TestCase;

/**
 * Checks what is really sent to Stripe, faking only the HTTP layer of its SDK.
 */
class StripeApiRequestsTest extends TestCase
{
    use RefreshDatabase;

    private object $http;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.secret' => 'sk_test_key', 'cashier.secret' => 'sk_test_key']);

        $this->http = new class implements ClientInterface
        {
            public array $requests = [];

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
            {
                $this->requests[] = compact('method', 'absUrl', 'headers', 'params');

                $body = match (true) {
                    str_contains($absUrl, '/v1/customers') => [
                        'id' => 'cus_1',
                        'object' => 'customer',
                        'invoice_settings' => [
                            'default_payment_method' => ['id' => 'pm_1', 'object' => 'payment_method', 'type' => 'card', 'customer' => 'cus_1'],
                        ],
                    ],
                    str_contains($absUrl, '/v1/payment_intents') => [
                        'id' => 'pi_1',
                        'object' => 'payment_intent',
                        'status' => 'succeeded',
                        'client_secret' => 'pi_1_secret',
                    ],
                };

                return [json_encode($body), 200, []];
            }

            public function intent(): array
            {
                return collect($this->requests)
                    ->first(fn (array $request) => $request['method'] === 'post' && str_ends_with($request['absUrl'], '/v1/payment_intents'));
            }
        };

        ApiRequestor::setHttpClient($this->http);
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(null);

        parent::tearDown();
    }

    #[Test]
    public function saved_cards_are_charged_without_redirect_based_payment_methods()
    {
        $this->signIn(create(User::class, ['stripe_id' => 'cus_1']));

        $payment = resolve(StripeApi::class)->createConfirmedPaymentIntent(10.5);

        $this->assertSame('pi_1', $payment->asStripePaymentIntent()->id);

        $request = $this->http->intent();

        $this->assertSame(['card'], $request['params']['payment_method_types']);
        $this->assertArrayNotHasKey('automatic_payment_methods', $request['params']);
        $this->assertSame('pm_1', $request['params']['payment_method']);
        $this->assertSame('cus_1', $request['params']['customer']);
        $this->assertSame('1050', $request['params']['amount']);
        $this->assertSame('true', $request['params']['confirm']);
    }

    #[Test]
    public function checkout_intents_keep_automatic_payment_methods()
    {
        $this->signIn(create(User::class, ['stripe_id' => 'cus_1']));

        resolve(StripeApi::class)->createPaymentIntent(20, 'buyer@mail.com');

        $request = $this->http->intent();

        $this->assertEquals(['enabled' => true], $request['params']['automatic_payment_methods']);
        $this->assertArrayNotHasKey('payment_method_types', $request['params']);
        $this->assertSame('buyer@mail.com', $request['params']['receipt_email']);
    }

    #[Test]
    public function guests_are_not_sent_as_stripe_customers()
    {
        resolve(StripeApi::class)->createPaymentIntent(20, 'guest@mail.com');

        $request = $this->http->intent();

        $this->assertArrayNotHasKey('customer', $request['params']);
        $this->assertArrayNotHasKey('setup_future_usage', $request['params']);
    }

    #[Test]
    public function every_request_uses_the_api_version_cashier_is_built_for()
    {
        $this->signIn(create(User::class, ['stripe_id' => 'cus_1']));

        resolve(StripeApi::class)->createConfirmedPaymentIntent(10);

        $this->assertNotEmpty($this->http->requests);

        foreach ($this->http->requests as $request) {
            $this->assertContains('Stripe-Version: '.ApiVersion::CURRENT, $request['headers']);
        }
    }
}
