<?php

namespace Tests\Feature\Stripe;

use App\Api\StripeApi;
use App\Enums\ProductTypes;
use App\Events\OrderSuccess;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Laravel\Cashier\Payment;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Stripe\PaymentIntent;
use Tests\TestCase;

class PayNowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Track $track;

    private Cart $cart;

    private User $seller;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->user = create(User::class, [
            'pm_type' => 'someType',
        ]);
        $this->seller = create(User::class);
        $this->track = create(Track::class, ['created_by' => $this->seller->id]);
        $this->product = create(Product::class, [
            'product_type' => ProductTypes::TRACK,
            'created_by' => $this->seller->id,
            'orderable_type' => Track::class,
            'orderable_id' => $this->track->id,
        ]);
        $this->cart = create(Cart::class, [
            'created_by' => $this->user->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ]);
        $this->signIn($this->user);
    }

    #[Test]
    public function it_let_authenticated_user_do_a_payment_with_default_payment_method()
    {
        $paymentIntent = new PaymentIntent;
        $paymentIntent->status = PaymentIntent::STATUS_SUCCEEDED;
        $paymentIntent->client_secret = 'some_secret';
        $payment = new Payment($paymentIntent);

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($payment) {
            $mock->shouldReceive('createConfirmedPaymentIntent')
                ->once()
                ->with($this->cart->getTotal())
                ->andReturn($payment);

            $mock->shouldReceive('getPaymentIntentId')
                ->once()
                ->with($payment)
                ->andReturn('someId');

            $mock->shouldReceive('isOrderPaid')
                ->once()
                ->andReturn(true);
        });

        $response = $this->makeRequest()
            ->assertSuccessful();

        /** @var Order $order */
        $order = Order::first();

        $this->assertEquals('some_secret', $response['secret']);
        $this->assertEquals('someId', $response['data']['meta']['payment_intent_id']);
        $this->assertEquals('someId', $order['meta']['payment_intent_id']);

        Event::assertDispatched(OrderSuccess::class);
    }

    #[Test]
    public function it_wont_create_an_order_if_payment_is_not_correctly_created()
    {
        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('createConfirmedPaymentIntent')
                ->once()
                ->with($this->cart->getTotal())
                ->andReturn(null);
        });

        $this->makeRequest()
            ->assertNoContent();

        Event::assertNotDispatched(OrderSuccess::class);
    }

    #[Test]
    public function it_wont_create_an_order_if_payment_is_not_correctly_validated()
    {
        $paymentIntent = new PaymentIntent;
        $paymentIntent->status = PaymentIntent::STATUS_REQUIRES_ACTION;
        $payment = new Payment($paymentIntent);

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($payment) {
            $mock->shouldReceive('createConfirmedPaymentIntent')
                ->once()
                ->with($this->cart->getTotal())
                ->andReturn($payment);
        });

        $this->makeRequest()
            ->assertNoContent();

        Event::assertNotDispatched(OrderSuccess::class);
    }

    #[Test]
    public function it_wont_let_pay_now_if_user_has_not_default_payment_method()
    {
        $this->user
            ->forceFill(['pm_type' => null])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();

        Event::assertNotDispatched(OrderSuccess::class);
    }

    #[Test]
    public function it_wont_let_pay_now_if_user_has_not_cart()
    {
        $this->user->cart->delete();

        $this->makeRequest()
            ->assertUnprocessable();

        Event::assertNotDispatched(OrderSuccess::class);
    }

    #[Test]
    public function unauthenticated_user_cannot_pay_now()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();

        Event::assertNotDispatched(OrderSuccess::class);
    }

    private function makeRequest(): TestResponse
    {
        return $this->postJson(route('pay_now.store'));
    }
}
