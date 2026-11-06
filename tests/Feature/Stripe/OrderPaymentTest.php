<?php

namespace Tests\Feature\Stripe;

use App\Api\StripeApi;
use App\Enums\PaymentStates;
use App\Events\OrderSuccess;
use App\Jobs\HandlePaymentIntent;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Tests\TestCase;

/**
 * Stripe decides if an order is paid, not the browser: only its HTTP layer is faked here.
 */
class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    private object $stripe;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.secret' => 'sk_test_key', 'cashier.secret' => 'sk_test_key']);

        $this->stripe = new class implements ClientInterface
        {
            public ?array $paymentIntent = null;

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
            {
                return $this->paymentIntent
                    ? [json_encode($this->paymentIntent), 200, []]
                    : [json_encode(['error' => ['message' => 'No such payment_intent']]), 404, []];
            }
        };

        ApiRequestor::setHttpClient($this->stripe);
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(null);

        parent::tearDown();
    }

    #[Test]
    public function an_order_paid_in_stripe_is_completed()
    {
        Event::fake([OrderSuccess::class]);
        $this->stripeHasPayment();

        $this->visitSuccessPage($this->order())->assertSuccessful();

        Event::assertDispatched(OrderSuccess::class);
    }

    #[Test]
    #[DataProvider('unpaidPayments')]
    public function an_order_is_not_completed_by_visiting_the_success_page_without_paying(array $payment)
    {
        Event::fake([OrderSuccess::class]);
        $this->stripeHasPayment($payment);

        $this->visitSuccessPage($this->order())->assertSuccessful();

        Event::assertNotDispatched(OrderSuccess::class);
    }

    public static function unpaidPayments(): array
    {
        return [
            'waiting for a card' => [['status' => 'requires_payment_method', 'amount_received' => 0]],
            'waiting for 3D Secure' => [['status' => 'requires_action', 'amount_received' => 0]],
            'still processing' => [['status' => 'processing', 'amount_received' => 0]],
            'canceled' => [['status' => 'canceled', 'amount_received' => 0]],
            'paying less than the order' => [['amount_received' => 1998]],
            'paying a tiny part of the order' => [['amount_received' => 50]],
            'paying in another currency' => [['currency' => 'jpy']],
        ];
    }

    #[Test]
    public function an_order_is_not_completed_if_stripe_does_not_know_its_payment()
    {
        Event::fake([OrderSuccess::class]);

        $this->visitSuccessPage($this->order())->assertSuccessful();

        Event::assertNotDispatched(OrderSuccess::class);
    }

    #[Test]
    public function an_order_without_a_payment_is_not_completed()
    {
        Event::fake([OrderSuccess::class]);
        $this->stripeHasPayment();

        $this->visitSuccessPage($this->order(['meta' => null]))->assertSuccessful();

        Event::assertNotDispatched(OrderSuccess::class);
    }

    #[Test]
    public function a_payment_only_completes_one_order()
    {
        Event::fake([OrderSuccess::class]);
        $this->stripeHasPayment();
        $this->order(['payment_state' => PaymentStates::SUCCESS]);

        $this->visitSuccessPage($this->order())->assertSuccessful();

        Event::assertNotDispatched(OrderSuccess::class);
    }

    #[Test]
    public function an_order_already_completed_is_not_processed_again()
    {
        Event::fake([OrderSuccess::class]);
        $this->stripeHasPayment();
        $order = $this->order();

        $this->confirmWithWebhook($order);
        $order->forceFill(['payment_state' => PaymentStates::SUCCESS])->saveQuietly();
        $this->confirmWithWebhook($order);
        $this->visitSuccessPage($order)->assertSuccessful();

        Event::assertDispatchedTimes(OrderSuccess::class, 1);
    }

    #[Test]
    public function the_webhook_completes_paid_orders_without_a_session()
    {
        $this->stripeHasPayment();
        $order = $this->order();

        $this->confirmWithWebhook($order);

        $this->assertSame(PaymentStates::SUCCESS, $order->refresh()->payment_state);
        $this->assertNotNull($order->succeeded);
    }

    #[Test]
    public function the_webhook_does_not_complete_orders_that_stripe_has_not_fully_charged()
    {
        Event::fake([OrderSuccess::class]);
        $this->stripeHasPayment(['amount_received' => 50]);

        $this->confirmWithWebhook($this->order());

        Event::assertNotDispatched(OrderSuccess::class);
    }

    #[Test]
    public function the_webhook_completes_the_last_order_of_a_payment_that_was_retried()
    {
        Event::fake([OrderSuccess::class]);
        $this->stripeHasPayment();
        $this->order();
        $retried = $this->order();

        $this->confirmWithWebhook($retried);

        Event::assertDispatched(OrderSuccess::class, fn (OrderSuccess $event) => $event->order->is($retried));
    }

    #[Test]
    public function a_failed_payment_does_not_undo_a_completed_order()
    {
        $this->stripeHasPayment();
        $order = $this->order(['payment_state' => PaymentStates::SUCCESS]);

        (new HandlePaymentIntent($this->payload($order), 'payment_intent.payment_failed'))->handle(resolve(PaymentService::class));

        $this->assertSame(PaymentStates::SUCCESS, $order->refresh()->payment_state);
    }

    #[Test]
    public function amounts_are_converted_to_cents_without_losing_any()
    {
        $this->stripeHasPayment(['amount_received' => 1999]);

        // 19.99 * 100 is 1998.9999999999998 in floating point
        $this->assertTrue(resolve(StripeApi::class)->isOrderPaid($this->order()));
    }

    private function order(array $attributes = []): Order
    {
        return create(Order::class, array_merge([
            'created_by' => null,
            'payment_state' => PaymentStates::PENDING,
            'total' => 19.99,
            'meta' => ['payment_intent_id' => 'pi_1'],
        ], $attributes));
    }

    private function stripeHasPayment(array $payment = []): void
    {
        $this->stripe->paymentIntent = array_merge([
            'id' => 'pi_1',
            'object' => 'payment_intent',
            'status' => 'succeeded',
            'amount' => 1999,
            'amount_received' => 1999,
            'currency' => 'eur',
        ], $payment);
    }

    private function visitSuccessPage(Order $order): TestResponse
    {
        return $this->get(URL::signedRoute('orders.success', ['order' => $order->id]));
    }

    private function confirmWithWebhook(Order $order): void
    {
        (new HandlePaymentIntent($this->payload($order), 'payment_intent.succeeded'))->handle(resolve(PaymentService::class));
    }

    private function payload(Order $order): array
    {
        return ['data' => ['object' => ['id' => $order->meta['payment_intent_id']]]];
    }
}
