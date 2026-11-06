<?php

namespace Tests\Feature\Services;

use App\Enums\PaymentStates;
use App\Enums\ProductTypes;
use App\Helpers\Calculator;
use App\Models\Cart;
use App\Models\Design;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    private User $user;

    private float $promoPrice;

    private float $pointsPrice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->service = resolve(OrderService::class);
        $this->promoPrice = config('company.services.promo.price');
        $this->pointsPrice = config('company.buy.points.price');
    }

    #[Test]
    public function it_can_create_an_order_from_cart_with_track_items()
    {
        /** @var User $owner */
        $owner = create(User::class);
        /** @var Track $track */
        $track = create(Track::class, ['created_by' => $owner->id]);

        $product = create(Product::class, [
            'created_by' => $owner->id,
            'name' => $track->name,
            'product_type' => ProductTypes::TRACK,
            'orderable_type' => Track::class,
            'orderable_id' => $track->id,
            'price' => 100,
        ]);

        $cart = create(Cart::class, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $order = $this->service->createFromCart($cart, 'some@mail.com');

        $this->assertEquals('some@mail.com', $order->email);
        $this->assertEquals($this->user->id, $order->created_by);
        $this->assertEquals(PaymentStates::PENDING, $order->payment_state);
        $this->assertEquals(100, $order->brute);
        $this->assertEquals(0, $order->tax);
        $this->assertEquals(0, $order->discount);
        $this->assertEquals(100, $order->total);
        $this->assertCount(1, $order->orderLines);
    }

    #[Test]
    public function it_can_create_an_order_from_cart_with_design_items()
    {
        /** @var User $owner */
        $owner = create(User::class);
        /** @var Design $design */
        $design = create(Design::class, ['created_by' => $owner->id]);
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => $owner->id,
            'name' => $design->name,
            'product_type' => ProductTypes::DESIGN,
            'orderable_type' => Design::class,
            'orderable_id' => $design->id,
            'price' => 100,
        ]);
        /** @var Cart $cart */
        $cart = create(Cart::class, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $order = $this->service->createFromCart($cart, 'some@mail.com');

        $this->assertEquals('some@mail.com', $order->email);
        $this->assertEquals($this->user->id, $order->created_by);
        $this->assertEquals(PaymentStates::PENDING, $order->payment_state);
        $this->assertEquals(100, $order->brute);
        $this->assertEquals(0, $order->tax);
        $this->assertEquals(0, $order->discount);
        $this->assertEquals(100, $order->total);
        $this->assertCount(1, $order->orderLines);
    }

    #[Test]
    public function it_can_create_an_order_from_cart_with_promo_items()
    {
        /** @var Design $design */
        $design = create(Design::class, ['created_by' => $this->user->id]);
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => null,
            'name' => 'Promoting a Design',
            'product_type' => ProductTypes::PROMO,
            'orderable_type' => Design::class,
            'orderable_id' => $design->id,
            'price' => $this->promoPrice,
        ]);
        /** @var Cart $cart */
        $cart = create(Cart::class, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'orderable_type' => Design::class,
                    'orderable_id' => $design->id,
                ],
            ],
        ]);

        $order = $this->service->createFromCart($cart, 'some@mail.com');

        $this->assertEquals('some@mail.com', $order->email);
        $this->assertEquals($this->user->id, $order->created_by);
        $this->assertEquals(PaymentStates::PENDING, $order->payment_state);
        $this->assertEquals(10, $order->brute);
        $this->assertEquals(10 * (Calculator::TAX_21 / 100), $order->tax_amount);
        $this->assertEquals(0, $order->discount);
        $this->assertEquals(10 * (1 + Calculator::TAX_21 / 100), $order->total);
    }

    #[Test]
    public function it_can_create_an_order_from_cart_with_points_items()
    {
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => null,
            'name' => 'Buy Points',
            'product_type' => ProductTypes::POINTS,
            'orderable_type' => null,
            'orderable_id' => null,
            'price' => $this->pointsPrice,
        ]);
        /** @var Cart $cart */
        $cart = create(Cart::class, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 50,
                    'orderable_type' => null,
                    'orderable_id' => null,
                ],
            ],
        ]);

        $order = $this->service->createFromCart($cart, 'some@mail.com');

        $total = $this->pointsPrice * 50;
        $taxAmount = $total * Calculator::TAX_21 / 100;

        $this->assertEquals('some@mail.com', $order->email);
        $this->assertEquals($this->user->id, $order->created_by);
        $this->assertEquals(PaymentStates::PENDING, $order->payment_state);
        $this->assertEquals($total, $order->brute);
        $this->assertEquals($taxAmount, $order->tax_amount);
        $this->assertEquals(0, $order->discount);
        $this->assertEquals($total + $taxAmount, $order->total);
        $this->assertCount(1, $order->orderLines);
    }

    #[Test]
    public function it_can_create_an_order_from_cart_with_donation_items()
    {
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => null,
            'name' => 'Doing a Donation',
            'product_type' => ProductTypes::DONATION,
            'orderable_type' => null,
            'orderable_id' => null,
            'price' => 1,
        ]);
        /** @var Cart $cart */
        $cart = create(Cart::class, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 100,
                    'orderable_type' => null,
                    'orderable_id' => null,
                ],
            ],
        ]);

        $order = $this->service->createFromCart($cart, 'some@mail.com');

        $this->assertEquals('some@mail.com', $order->email);
        $this->assertEquals($this->user->id, $order->created_by);
        $this->assertEquals(PaymentStates::PENDING, $order->payment_state);
        $this->assertCount(1, $order->orderLines);
        $this->assertEquals(PaymentStates::PENDING, $order->payment_state);
        $this->assertEquals(100, $order->brute);
        $this->assertEquals(0, $order->tax_amount);
        $this->assertEquals(0, $order->discount);
        $this->assertEquals(100, $order->total);
    }

    #[Test]
    public function it_can_create_an_order_from_cart_with_points_items_to_unauthenticated_user()
    {
        $this->logout();

        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => null,
            'name' => 'Buy Points',
            'product_type' => ProductTypes::POINTS,
            'orderable_type' => null,
            'orderable_id' => null,
            'price' => $this->pointsPrice,
        ]);
        /** @var Cart $cart */
        $cart = create(Cart::class, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 50,
                    'orderable_type' => null,
                    'orderable_id' => null,
                ],
            ],
        ]);

        $order = $this->service->createFromCart($cart, 'some@mail.com');

        $this->assertNull($order->created_by);
        $this->assertEquals('some@mail.com', $order->email);
    }
}
