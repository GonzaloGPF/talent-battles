<?php

namespace Tests\Feature\Orders;

use App\Api\StripeApi;
use App\Enums\ProductTypes;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Stripe\PaymentIntent;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $owner;

    private Track $track;

    private Product $product;

    private Cart $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->owner = create(User::class);
        $this->track = create(Track::class, ['created_by' => $this->owner->id]);
        $this->product = create(Product::class, [
            'created_by' => $this->owner->id,
            'product_type' => ProductTypes::TRACK,
            'orderable_type' => Track::class,
            'orderable_id' => $this->track->id,
        ]);
        $this->cart = create(Cart::class, [
            'created_by' => $this->user->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'created_by' => $this->user->id,
                    'description' => 'Some Description',
                    'product_type' => ProductTypes::TRACK->value,
                    'orderable_type' => Track::class,
                    'orderable_id' => $this->track->id,
                    'seller_id' => $this->owner->id,
                ],
            ],
        ]);

        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrievePaymentIntent')
                ->once()
                ->andReturn(new PaymentIntent);
        });
    }

    #[Test]
    public function it_let_create_an_order_from_cart()
    {
        $response = $this->makeRequest()
            ->assertSuccessful();

        $order = Order::first();

        $this->assertEquals(URL::signedRoute('orders.success', compact('order')), $response['return_url']);
        $this->assertEquals($this->user->email, $order->email);
    }

    #[Test]
    public function it_wont_create_an_order_if_cart_is_empty()
    {
        $this->cart->update(['items' => null]);

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_will_require_an_email_if_its_a_guest_user()
    {
        $this->logout();

        $this->cart->forceFill([
            'created_by' => null,
            'guest_id' => CartService::getOrCreateGuestId(),
        ])->saveQuietly();

        $this->makeRequest(['email' => null])
            ->assertUnprocessable();
    }

    #[Test]
    public function it_let_guest_user_create_an_order_if_gives_its_email()
    {
        $this->logout();

        $this->cart->forceFill([
            'created_by' => null,
            'guest_id' => CartService::getOrCreateGuestId(),
        ])->saveQuietly();

        $response = $this->makeRequest(['email' => 'guest@mail.com'])
            ->assertSuccessful();

        $order = Order::first();

        $this->assertEquals(URL::signedRoute('orders.success', compact('order')), $response['return_url']);
        $this->assertEquals('guest@mail.com', $order->email);
    }

    #[Test]
    public function it_wont_let_create_an_order_if_cart_is_empty()
    {
        $this->cart->forceFill([
            'items' => null,
        ])->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function the_order_keeps_what_the_buyer_accepted()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $order = Order::first();

        $this->assertTrue($order->meta['terms_accepted']);
        $this->assertTrue($order->meta['immediate_delivery']);
    }

    #[Test]
    public function the_buyer_has_to_accept_the_terms()
    {
        $this->makeRequest(['terms_and_conditions' => false])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['terms_and_conditions' => trans('help.must_accept_terms_and_conditions')], 'data');

        $this->assertDatabaseEmpty('orders');
    }

    #[Test]
    public function the_buyer_has_to_ask_for_the_immediate_delivery_of_what_is_delivered_when_paid()
    {
        $this->makeRequest(['immediate_delivery' => false])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['immediate_delivery' => trans('help.must_accept_immediate_delivery')], 'data');

        $this->assertDatabaseEmpty('orders');
    }

    #[Test]
    public function a_donation_is_not_delivered_so_it_does_not_ask_for_immediate_delivery()
    {
        $donation = create(Product::class, ['product_type' => ProductTypes::DONATION]);
        $this->cart->forceFill([
            'items' => [['product_id' => $donation->id, 'product_type' => ProductTypes::DONATION->value]],
        ])->saveQuietly();

        $this->makeRequest(['immediate_delivery' => false])
            ->assertSuccessful();

        $this->assertFalse(Order::first()->meta['immediate_delivery']);
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('orders.store'), [
            'payment_intent_id' => 'someId',
            'terms_and_conditions' => true,
            'immediate_delivery' => true,
            ...$attributes,
        ]);
    }
}
