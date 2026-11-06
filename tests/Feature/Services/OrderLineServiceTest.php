<?php

namespace Tests\Feature\Services;

use App\Enums\FileTypes;
use App\Enums\ProductTypes;
use App\Enums\TrackTypes;
use App\Models\Cart;
use App\Models\Design;
use App\Models\Donation;
use App\Models\File;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use App\Services\FileService;
use App\Services\OrderLineService;
use App\Services\PointsService;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderLineServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderLineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->user = $this->signIn();
        $this->service = resolve(OrderLineService::class);
    }

    #[Test]
    public function it_can_make_lines_from_cart_items_data_of_a_track()
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
            'orderable_id' => $track->id,
            'orderable_type' => Track::class,
            'name' => $track->name,
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

        $orderLines = $this->service->makeFromCart($cart);

        /** @var OrderLine $orderLine */
        $orderLine = $orderLines->first();

        $this->assertEquals($this->user->id, $orderLine->created_by);
        $this->assertEquals(ProductTypes::TRACK, $orderLine->product_type);
        $this->assertEquals($product->id, $orderLine->product_id);
        $this->assertEquals($owner->id, $orderLine->seller_id);
        $this->assertEquals($track->id, $orderLine->orderable_id);
        $this->assertEquals(Track::class, $orderLine->orderable_type);
        $this->assertEquals(trans('help.buy_of', ['description' => $product->name]), $orderLine->description);
        $this->assertEquals(100, $orderLine->price);
        $this->assertEquals(1, $orderLine->quantity);
        $this->assertEquals(100, $orderLine->total);
    }

    #[Test]
    public function it_can_make_lines_from_cart_items_data_of_a_design()
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
            'orderable_id' => $design->id,
            'orderable_type' => Design::class,
            'name' => $design->name,
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

        $orderLines = $this->service->makeFromCart($cart);
        /** @var OrderLine $orderLine */
        $orderLine = $orderLines->first();

        $this->assertEquals($this->user->id, $orderLine->created_by);
        $this->assertEquals(ProductTypes::DESIGN, $orderLine->product_type);
        $this->assertEquals($product->id, $orderLine->product_id);
        $this->assertEquals($owner->id, $orderLine->seller_id);
        $this->assertEquals($design->id, $orderLine->orderable_id);
        $this->assertEquals(Design::class, $orderLine->orderable_type);
        $this->assertEquals(trans('help.buy_of', ['description' => $product->name]), $orderLine->description);
        $this->assertEquals(100, $orderLine->price);
        $this->assertEquals(1, $orderLine->quantity);
        $this->assertEquals(100, $orderLine->total);
    }

    #[Test]
    public function it_can_make_lines_from_cart_items_data_with_promo()
    {
        $price = config('company.services.promo.price');

        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => $this->user->id,
        ]);

        /** @var Product $promoProduct */
        $promoProduct = create(Product::class, [
            'created_by' => null,
            'product_type' => ProductTypes::PROMO,
            'orderable_id' => null,
            'orderable_type' => null,
            'name' => $track->name,
            'price' => $price,
            'promoted' => null,
        ]);

        /** @var Product $trackProduct */
        $trackProduct = create(Product::class, [
            'created_by' => null,
            'product_type' => ProductTypes::TRACK,
            'orderable_id' => $track->id,
            'orderable_type' => Track::class,
            'name' => $track->name,
            'price' => 100,
            'promoted' => null,
        ]);

        /** @var Cart $cart */
        $cart = create(Cart::class, [
            'items' => [
                [
                    'product_id' => $promoProduct->id,
                    'orderable_id' => $trackProduct->id,
                    'orderable_type' => Product::class,
                    'quantity' => 2,
                ],
            ],
        ]);

        $orderLines = $this->service->makeFromCart($cart);
        /** @var OrderLine $orderLine */
        $orderLine = $orderLines->first();

        $this->assertEquals($this->user->id, $orderLine->created_by);
        $this->assertEquals(ProductTypes::PROMO, $orderLine->product_type);
        $this->assertEquals($promoProduct->id, $orderLine->product_id);
        $this->assertNull($orderLine->seller_id);
        $this->assertEquals($trackProduct->id, $orderLine->orderable_id);
        $this->assertEquals(Product::class, $orderLine->orderable_type);
        $this->assertEquals(trans('help.featuring_creation', [
            'creation' => $track->name,
            'date' => today()->addMonths(2)->toDateString(),
        ]), $orderLine->description);
        $this->assertEquals($price, $orderLine->price);
        $this->assertEquals(2, $orderLine->quantity);
        $this->assertEquals($price * 2, $orderLine->total);
    }

    #[Test]
    public function it_can_make_lines_from_cart_items_data_with_points()
    {
        $price = config('company.buy.points.price');

        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => null,
            'product_type' => ProductTypes::POINTS,
            'orderable_id' => null,
            'orderable_type' => null,
            'name' => 'Points',
            'price' => $price,
        ]);
        /** @var Cart $cart */
        $cart = create(Cart::class, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 50,
                ],
            ],
        ]);

        $orderLines = $this->service->makeFromCart($cart);
        /** @var OrderLine $orderLine */
        $orderLine = $orderLines->first();

        $this->assertEquals($this->user->id, $orderLine->created_by);
        $this->assertEquals(ProductTypes::POINTS, $orderLine->product_type);
        $this->assertNull($orderLine->seller_id);
        $this->assertNull($orderLine->orderable_id);
        $this->assertNull($orderLine->orderable_type);
        $this->assertEquals(trans('help.buying_points', ['points' => 50]), $orderLine->description);
        $this->assertEquals($price, $orderLine->price);
        $this->assertEquals(50, $orderLine->quantity);
        $this->assertEquals($price * 50, $orderLine->total);
    }

    #[Test]
    public function it_can_make_lines_from_cart_items_data_with_donation()
    {
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => null,
            'product_type' => ProductTypes::DONATION,
            'orderable_id' => null,
            'orderable_type' => null,
            'name' => 'Donation',
            'price' => 1,
        ]);
        /** @var Cart $cart */
        $cart = create(Cart::class, [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 100,
                    'description' => 'Some Donation',
                ],
            ],
        ]);

        $orderLines = $this->service->makeFromCart($cart);
        /** @var OrderLine $orderLine */
        $orderLine = $orderLines->first();

        $this->assertEquals($this->user->id, $orderLine->created_by);
        $this->assertEquals(ProductTypes::DONATION, $orderLine->product_type);
        $this->assertNull($orderLine->seller_id);
        $this->assertEquals($product->id, $orderLine->product_id);
        $this->assertEquals('Some Donation', $orderLine->description);
        $this->assertEquals(1, $orderLine->price);
        $this->assertEquals(100, $orderLine->quantity);
        $this->assertEquals(100, $orderLine->total);
    }

    #[Test]
    public function it_can_process_order_lines_of_track_creation()
    {
        /** @var User $owner */
        $owner = create(User::class);
        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => $owner->id,
        ]);
        /** @var Product $product */
        $product = create(Product::class, [
            'orderable_type' => Track::class,
            'orderable_id' => $track->id,
            'last_sale' => null,
            'sales' => 0,
        ]);
        $file = create(File::class, [
            'created_by' => $owner->id,
            'file_type' => FileTypes::AUDIO,
            'path' => generateTestFile('test.mp3'),
            'fileable_type' => Product::class,
            'fileable_id' => $product->id,
        ]);

        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, [
            'created_by' => $this->user->id,
            'seller_id' => $owner->id,
            'orderable_type' => Track::class,
            'orderable_id' => $track->id,
            'product_type' => ProductTypes::TRACK,
        ]);

        $this->service->processCreation($orderLine);

        tap($product->fresh(), function (Product $product) {
            $this->assertEquals(1, $product->sales);
            $this->assertNotNull($product->last_sale);
        });

        tap($orderLine->fresh(), function (OrderLine $orderLine) {
            $this->assertEquals(FileTypes::AUDIO, $orderLine->file->file_type);
            $this->assertEquals($this->user->id, $orderLine->file->created_by);
            $this->assertNotNull($orderLine->file->path);
        });
    }

    #[Test]
    public function it_wont_process_order_lines_of_track_creation_if_its_not_a_creation()
    {
        /** @var User $owner */
        $owner = create(User::class);
        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => $owner->id,
            'track_type' => TrackTypes::BEAT,
        ]);

        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, [
            'created_by' => $this->user->id,
            'seller_id' => $owner->id,
            'orderable_type' => Track::class,
            'orderable_id' => $track->id,
            'product_type' => ProductTypes::POINTS,
        ]);

        $this->service->processCreation($orderLine);

        tap($track->fresh(), function (Track $track) {
            $this->assertEquals(0, $track->sales);
            $this->assertNull($track->last_sale);
        });
    }

    #[Test]
    public function it_wont_process_order_lines_of_track_creation_if_product_does_not_have_product()
    {
        $this->mock(FileService::class, function (MockInterface $mock) {
            $mock->shouldNotHaveReceived('copyFile');
        });
        /** @var User $owner */
        $owner = create(User::class);
        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => $owner->id,
            'track_type' => TrackTypes::BEAT,
        ]);

        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, [
            'created_by' => $this->user->id,
            'seller_id' => $owner->id,
            'orderable_type' => Track::class,
            'orderable_id' => $track->id,
            'product_type' => ProductTypes::TRACK,
        ]);

        $this->service->processCreation($orderLine);
    }

    #[Test]
    public function it_can_process_order_lines_of_promo()
    {
        /** @var Track $track */
        $track = create(Track::class);

        $promoProduct = create(Product::class, [
            'product_type' => ProductTypes::PROMO,
            'orderable_type' => null,
            'orderable_id' => null,
        ]);

        /** @var Product $trackProduct */
        $trackProduct = create(Product::class, [
            'promoted' => null,
            'product_type' => ProductTypes::TRACK,
            'orderable_type' => Track::class,
            'orderable_id' => $track->id,
        ]);

        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, [
            'product_id' => $promoProduct->id,
            'created_by' => $this->user->id,
            'quantity' => 2,
            'orderable_type' => Product::class,
            'orderable_id' => $trackProduct->id,
            'product_type' => ProductTypes::PROMO,
        ]);

        $this->service->processPromo($orderLine);

        $this->assertEquals(today()->addMonths(2)->toDateString(), $trackProduct->fresh()->promoted->toDateString());
    }

    #[Test]
    public function it_wont_process_order_lines_of_promo_if_its_not_promo_line()
    {
        /** @var Track $track */
        $track = create(Track::class, [
            'track_type' => TrackTypes::BEAT,
        ]);
        /** @var Product $trackProduct */
        $trackProduct = create(Product::class, [
            'product_type' => ProductTypes::TRACK,
            'orderable_id' => $track->id,
            'orderable_type' => Track::class,
            'promoted' => null,
        ]);

        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, [
            'quantity' => 2,
            'orderable_type' => Track::class,
            'orderable_id' => $track->id,
            'product_type' => ProductTypes::POINTS,
        ]);

        $this->service->processPromo($orderLine);

        $this->assertNull($trackProduct->fresh()->promoted);
    }

    #[Test]
    public function it_can_process_order_lines_of_points()
    {
        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, [
            'quantity' => 50,
            'product_type' => ProductTypes::POINTS,
        ]);

        /** @var OrderLine $orderLine2 */
        $orderLine2 = create(OrderLine::class, [
            'quantity' => 5,
            'product_type' => ProductTypes::PROMO, // should be ignored
        ]);

        $this->mock(PointsService::class, function (MockInterface $mock) {
            $mock->shouldReceive('earnPoints')
                ->once()
                ->with(50, $this->user, true);
        });

        $this->service->processPoints(collect([$orderLine, $orderLine2]), $this->user);
    }

    #[Test]
    public function it_can_process_order_lines_of_donation()
    {
        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, [
            'description' => 'Some message',
            'quantity' => 50,
            'product_type' => ProductTypes::DONATION,
        ]);

        $this->service->processDonation($orderLine);

        /** @var Donation $donation */
        $donation = Donation::first();

        $this->assertEquals($this->user->id, $donation->created_by);
        $this->assertEquals('Some message', $donation->message);
        $this->assertEquals($orderLine->total_with_taxes, $donation->amount);
    }
}
