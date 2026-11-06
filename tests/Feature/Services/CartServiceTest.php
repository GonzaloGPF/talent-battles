<?php

namespace Tests\Feature\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Track;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(CartService::class);
    }

    #[Test]
    public function it_can_create_a_cart_for_logged_user()
    {
        $user = $this->signIn();

        $cart = $this->service->getOrCreateCart();

        $this->assertEquals($user->id, $cart->created_by);
        $this->assertNull($cart->guest_id);
    }

    #[Test]
    public function it_can_get_a_cart_for_logged_user()
    {
        $user = $this->signIn();

        create(Cart::class, ['created_by' => $user->id]);

        $cart = $this->service->getOrCreateCart();

        $this->assertEquals($user->id, $cart->created_by);
        $this->assertEquals(1, Cart::count());
    }

    #[Test]
    public function it_can_create_a_cart_for_guest_user()
    {
        $cart = $this->service->getOrCreateCart();

        $this->assertNull($cart->created_by);
        $this->assertEquals(CartService::getGuestId(), $cart->guest_id);
        $this->assertEquals(CartService::getGuestId(), session('cart_session_key'));
    }

    #[Test]
    public function it_can_get_a_cart_for_guest_user()
    {
        create(Cart::class, [
            'created_by' => null,
            'guest_id' => 'someId',
        ]);
        session()->put('cart_session_key', 'someId');

        $cart = $this->service->getOrCreateCart();

        $this->assertNull($cart->created_by);
        $this->assertEquals('someId', $cart->guest_id);
        $this->assertEquals(1, Cart::count());
    }

    #[Test]
    public function it_can_add_cart_items_to_a_given_cart()
    {
        /** @var Product $product */
        $product = create(Product::class);

        $cart = create(Cart::class, ['items' => []]);

        $cart = $this->service->addItems($cart, [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'orderable_id' => null,
                'orderable_type' => null,
            ],
        ]);

        $this->assertCount(1, $cart->fresh()->items);
    }

    #[Test]
    public function it_wont_add_cart_items_to_a_given_cart_if_its_duplicated()
    {
        /** @var Track $track */
        $track = create(Track::class);
        /** @var Product $product */
        $product = create(Product::class, [
            'orderable_id' => $track->id,
            'orderable_type' => Track::class,
        ]);

        $cart = create(Cart::class, ['items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'orderable_id' => $track->id,
                'orderable_type' => Track::class,
            ],
        ]]);

        $cart = $this->service->addItems($cart, [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'orderable_id' => $track->id,
                'orderable_type' => Track::class,
            ],
        ]);

        $this->assertCount(1, $cart->fresh()->items);
    }

    #[Test]
    public function it_let_remove_items_from_cart()
    {
        /** @var Track $track */
        $track = create(Track::class);
        /** @var Product $product */
        $product = create(Product::class, [
            'orderable_id' => $track->id,
            'orderable_type' => Track::class,
        ]);

        $cart = create(Cart::class, ['items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'orderable_id' => $track->id,
                'orderable_type' => Track::class,
            ],
        ]]);

        $cart = $this->service->removeItems($cart, [$product->id]);

        $this->assertCount(0, $cart->fresh()->items);
    }
}
