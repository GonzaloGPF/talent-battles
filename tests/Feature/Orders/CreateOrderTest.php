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

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('orders.store', array_merge([
            'payment_intent_id' => 'someId',
        ], $attributes)));
    }
}
