<?php

namespace Tests\Feature\Carts;

use App\Enums\FileTypes;
use App\Enums\ProductTypes;
use App\Models\Cart;
use App\Models\Design;
use App\Models\File;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use App\Objects\CartItem;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateCartTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $owner;

    private Track $track;

    private File $file;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
    }

    #[Test]
    public function user_can_add_track_items_to_cart()
    {
        /** @var User $owner */
        $owner = create(User::class);
        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => $owner->id,
        ]);
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => $owner->id,
            'product_type' => ProductTypes::TRACK,
            'orderable_type' => Track::class,
            'orderable_id' => $track->id,
            'price' => 100,
            'last_sale' => null,
            'exclusive' => null,
        ]);
        /** @var File $file */
        $file = create(File::class, [
            'created_by' => $owner->id,
            'fileable_type' => Product::class,
            'fileable_id' => $product->id,
            'file_type' => FileTypes::AUDIO,
            'path' => generateTestFile(),
        ]);

        /** @var Cart $cart */
        $cart = create(Cart::class, ['created_by' => $this->user->id]);

        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $product->id,
                    'orderable_id' => $track->id,
                    'orderable_type' => Track::class,
                ],
            ],
        ])->assertSuccessful();

        tap($cart->fresh(), function (Cart $cart) use ($track, $product) {
            /** @var CartItem $cartItem */
            $cartItem = $cart->items->first();

            $this->assertEquals($product->id, $cartItem->productId);
            $this->assertEquals($track->id, $cartItem->orderableId);
            $this->assertEquals(Track::class, $cartItem->orderableType);
            $this->assertEquals(ProductTypes::TRACK->value, $cartItem->productType->value);
        });
    }

    #[Test]
    public function user_can_add_design_items_to_cart()
    {
        /** @var User $owner */
        $owner = create(User::class);
        /** @var Design $design */
        $design = create(Design::class, [
            'created_by' => $owner->id,
        ]);
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => $owner->id,
            'product_type' => ProductTypes::DESIGN,
            'orderable_type' => Design::class,
            'orderable_id' => $design->id,
            'price' => 100,
            'last_sale' => null,
            'exclusive' => null,
        ]);
        /** @var File $file */
        $file = create(File::class, [
            'created_by' => $owner->id,
            'fileable_type' => Product::class,
            'fileable_id' => $product->id,
            'file_type' => FileTypes::IMAGE,
            'path' => generateTestFile(),
        ]);

        /** @var Cart $cart */
        $cart = create(Cart::class, ['created_by' => $this->user->id]);

        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $product->id,
                    'orderable_id' => $design->id,
                    'orderable_type' => Design::class,
                ],
            ],
        ])->assertSuccessful();

        tap($cart->fresh(), function (Cart $cart) use ($design, $product) {
            /** @var CartItem $cartItem */
            $cartItem = $cart->items->first();

            $this->assertEquals($product->id, $cartItem->productId);
            $this->assertEquals($design->id, $cartItem->orderableId);
            $this->assertEquals(Design::class, $cartItem->orderableType);
            $this->assertEquals(ProductTypes::DESIGN->value, $cartItem->productType->value);
        });
    }

    #[Test]
    public function user_can_add_promo_items_to_cart()
    {
        /** @var Design $design */
        $design = create(Design::class, [
            'created_by' => $this->user->id,
        ]);
        /** @var Product $designProduct */
        $designProduct = create(Product::class, [
            'created_by' => $this->user->id,
            'product_type' => ProductTypes::DESIGN,
            'orderable_type' => Design::class,
            'orderable_id' => $design->id,
            'price' => 100,
            'last_sale' => null,
            'exclusive' => null,
            'promoted' => null,
        ]);

        /** @var Product $promoProduct */
        $promoProduct = create(Product::class, [
            'created_by' => null,
            'product_type' => ProductTypes::PROMO,
            'orderable_type' => Design::class,
            'orderable_id' => $design->id,
            'price' => config('company.services.promo.price'),
            'last_sale' => null,
            'exclusive' => null,
        ]);

        /** @var Cart $cart */
        $cart = create(Cart::class, ['created_by' => $this->user->id]);

        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $promoProduct->id,
                    'orderable_id' => $designProduct->id,
                    'orderable_type' => Product::class,
                ],
            ],
        ])->assertSuccessful();

        tap($cart->fresh(), function (Cart $cart) use ($designProduct, $promoProduct) {
            /** @var CartItem $cartItem */
            $cartItem = $cart->items->first();

            $this->assertEquals($promoProduct->id, $cartItem->productId);
            $this->assertEquals($designProduct->id, $cartItem->orderableId);
            $this->assertEquals(Product::class, $cartItem->orderableType);
            $this->assertEquals(ProductTypes::PROMO->value, $cartItem->productType->value);
        });
    }

    #[Test]
    public function user_can_add_donation_items_to_cart()
    {
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => null,
            'product_type' => ProductTypes::DONATION,
            'orderable_type' => null,
            'orderable_id' => null,
            'price' => 1,
            'last_sale' => null,
            'exclusive' => null,
        ]);

        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'description' => 'Some description',
                ],
            ],
        ])->assertSuccessful();

        tap(Cart::first(), function (Cart $cart) use ($product) {
            /** @var CartItem $cartItem */
            $cartItem = $cart->items->first();

            $this->assertEquals($product->id, $cartItem->productId);
            $this->assertNull($cartItem->orderableId);
            $this->assertNull($cartItem->orderableType);
            $this->assertEquals(ProductTypes::DONATION->value, $cartItem->productType->value);
            $this->assertEquals(10, $cartItem->quantity);
            $this->assertEquals('Some description', $cartItem->description);
        });
    }

    #[Test]
    public function user_can_add_points_items_to_cart()
    {
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => null,
            'product_type' => ProductTypes::POINTS,
            'orderable_type' => null,
            'orderable_id' => null,
            'price' => config('company.buy.points.price'),
            'last_sale' => null,
            'exclusive' => null,
        ]);

        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 50,
                ],
            ],
        ])->assertSuccessful();

        tap(Cart::first(), function (Cart $cart) use ($product) {
            /** @var CartItem $cartItem */
            $cartItem = $cart->items->first();

            $this->assertEquals($product->id, $cartItem->productId);
            $this->assertNull($cartItem->orderableId);
            $this->assertNull($cartItem->orderableType);
            $this->assertEquals(ProductTypes::POINTS->value, $cartItem->productType->value);
            $this->assertEquals(50, $cartItem->quantity);
            $this->assertEquals(trans('help.buying_points', ['points' => 50]), $cartItem->description);
        });
    }

    #[Test]
    public function when_adding_items_to_cart_it_wont_let_add_same_item_twice()
    {
        $this->initTrackProduct();

        /** @var Cart $cart */
        $cart = create(Cart::class, ['created_by' => $this->user->id]);

        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $this->product->id,
                    'orderable_id' => $this->track->id,
                    'orderable_type' => Track::class,
                ],
                [
                    'product_id' => $this->product->id,
                    'orderable_id' => $this->track->id,
                    'orderable_type' => Track::class,
                ],
            ],
        ])->assertSuccessful();

        $this->assertCount(1, $cart->fresh()->items);
    }

    #[Test]
    public function when_adding_items_to_cart_it_will_create_a_cart_if_cart_doesnt_exist()
    {
        $this->initTrackProduct();

        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $this->product->id,
                    'orderable_id' => $this->track->id,
                    'orderable_type' => Track::class,
                ],
            ],
        ])->assertSuccessful();

        $cart = Cart::first();
        $this->assertEquals($this->user->id, $cart->created_by);

        /** @var CartItem $cartItem */
        $cartItem = $cart->items->first();
        $this->assertEquals($this->track->id, $cartItem->orderableId);
        $this->assertEquals(Track::class, $cartItem->orderableType);
    }

    #[Test]
    public function if_user_is_not_logged_it_will_create_a_cart_for_a_guest()
    {
        $this->initTrackProduct();

        $this->logout();

        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $this->product->id,
                    'orderable_id' => $this->track->id,
                    'orderable_type' => Track::class,
                ],
            ],
        ])->assertSuccessful();

        $this->assertEquals(CartService::getGuestId(), Cart::first()->guest_id);
    }

    #[Test]
    public function it_wont_let_add_items_to_cart_if_it_is_an_exclusive_product_already_sold()
    {
        $this->initTrackProduct();

        $this->product
            ->forceFill([
                'last_sale' => now(),
                'exclusive' => true,
            ])->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_add_items_to_cart_if_it_is_product_with_price_of_zero()
    {
        $this->initTrackProduct();

        $this->product
            ->forceFill(['price' => 0])
            ->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_add_items_to_cart_if_it_is_product_without_a_file()
    {
        $this->initTrackProduct();

        $this->file->delete();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_add_items_to_cart_if_it_is_product_already_ordered_by_user()
    {
        $this->initTrackProduct();

        /** @var OrderLine $orderLine */
        create(OrderLine::class, [
            'created_by' => $this->user->id,
            'orderable_type' => Track::class,
            'orderable_id' => $this->track->id,
        ]);

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_order_an_own_product()
    {
        $this->initTrackProduct();

        $this->signIn($this->owner);

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_promo_a_product_if_it_belongs_to_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        /** @var Track $foreignTrack */
        $foreignTrack = create(Track::class, ['created_by' => $otherUser->id]);
        /** @var Product $foreignTrackProduct */
        $foreignTrackProduct = create(Product::class, [
            'created_by' => $otherUser->id,
            'product_type' => ProductTypes::TRACK,
            'orderable_type' => Track::class,
            'orderable_id' => $foreignTrack->id,
        ]);
        /** @var Product $promoProduct */
        $promoProduct = create(Product::class, [
            'created_by' => null,
            'product_type' => ProductTypes::PROMO,
            'orderable_type' => null,
            'orderable_id' => null,
        ]);

        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $promoProduct->id,
                    'orderable_id' => $foreignTrackProduct->id,
                    'orderable_type' => Product::class,
                ],
            ],
        ])->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_buy_less_than_50_points()
    {
        /** @var Product $promoProduct */
        $promoProduct = create(Product::class, [
            'created_by' => null,
            'product_type' => ProductTypes::POINTS,
            'orderable_type' => null,
            'orderable_id' => null,
        ]);
        $this->makeRequest([
            'items_data' => [
                [
                    'product_id' => $promoProduct->id,
                    'quantity' => 49,
                ],
            ],
        ])->assertUnprocessable();
    }

    private function initTrackProduct(): void
    {
        $this->owner = create(User::class);
        /** @var Track $track */
        $this->track = create(Track::class, [
            'created_by' => $this->owner->id,
        ]);
        $this->product = create(Product::class, [
            'created_by' => $this->owner->id,
            'product_type' => ProductTypes::TRACK,
            'orderable_type' => Track::class,
            'orderable_id' => $this->track->id,
            'price' => 100,
            'last_sale' => null,
            'exclusive' => null,
        ]);
        $this->file = create(File::class, [
            'created_by' => $this->owner->id,
            'fileable_type' => Product::class,
            'fileable_id' => $this->product->id,
            'file_type' => FileTypes::AUDIO,
            'path' => generateTestFile(),
        ]);
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('carts.update'), $attributes);
    }
}
