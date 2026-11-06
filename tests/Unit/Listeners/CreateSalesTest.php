<?php

namespace Tests\Unit\Listeners;

use App\Enums\ProductTypes;
use App\Events\OrderSuccess;
use App\Events\SalesCreated;
use App\Listeners\CreateSales;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateSalesTest extends TestCase
{
    private Order $order;

    private OrderLine $orderLine1;

    private OrderLine $orderLine2;

    private OrderLine $orderLine3;

    private OrderLine $orderLine4;

    private OrderLine $orderLine5;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = new Order;
        $user1 = new User;
        $user1->forceFill(['id' => 1]);
        $this->orderLine1 = (new OrderLine)->forceFill([
            'product_type' => ProductTypes::TRACK,
            'seller_id' => $user1->id,
        ]);
        $user2 = new User;
        $user2->forceFill(['id' => 2]);
        $this->orderLine2 = (new OrderLine)->forceFill([
            'product_type' => ProductTypes::DESIGN,
            'seller_id' => $user2->id,
        ]);
        $user3 = new User;
        $user3->forceFill(['id' => 3]);
        $this->orderLine3 = (new OrderLine)->forceFill([ // Should be ignored
            'product_type' => ProductTypes::POINTS,
            'seller_id' => $user3->id,
        ]);
        $user4 = new User;
        $user4->forceFill(['id' => 4]);
        $this->orderLine4 = (new OrderLine)->forceFill([ // Should be ignored
            'product_type' => ProductTypes::PROMO,
            'seller_id' => $user4->id,
        ]);
        $user5 = new User;
        $user5->forceFill(['id' => 5]);
        $this->orderLine5 = (new OrderLine)->forceFill([ // Should be ignored
            'product_type' => ProductTypes::DONATION,
            'seller_id' => $user5->id,
        ]);
        $this->order->setRelation('orderLines', collect([
            $this->orderLine1,
            $this->orderLine2,
            $this->orderLine3,
            $this->orderLine4,
            $this->orderLine5,
        ]));
    }

    #[Test]
    public function it_listens_to_order_success()
    {
        Event::fake();
        Event::assertListening(OrderSuccess::class, CreateSales::class);
    }

    #[Test]
    public function it_creates_sales_when_order_success_is_dispatched()
    {
        Event::fake();

        $this->mock(SaleService::class, function (MockInterface $mock) {
            $mockedSale = (new Sale)->forceFill(['created_by' => 1]);
            $mock->shouldReceive('createFrom')
                ->once()
                ->andReturn($mockedSale);

            $mockedSale2 = (new Sale)->forceFill(['created_by' => 2]);
            $mock->shouldReceive('createFrom')
                ->once()
                ->andReturn($mockedSale2);
        });

        $event = new OrderSuccess($this->order);
        $listener = resolve(CreateSales::class);
        $listener->handle($event);

        Event::assertDispatched(SalesCreated::class, function (SalesCreated $salesCreated) {
            return $salesCreated->sales->count() === 2;
        });
    }
}
