<?php

namespace Listeners;

use App\Enums\ProductTypes;
use App\Events\OrderSuccess;
use App\Listeners\ProcessDonationLines;
use App\Models\Order;
use App\Models\OrderLine;
use App\Services\OrderLineService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessDonationLinesTest extends TestCase
{
    private Order $order;

    private OrderLine $orderLine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = new Order;
        $this->orderLine = (new OrderLine)->forceFill([
            'product_type' => ProductTypes::DONATION,
        ]);
        $this->order->setRelation('orderLines', collect([
            $this->orderLine,
        ]));
    }

    #[Test]
    public function it_listens_to_order_success()
    {
        Event::fake();
        Event::assertListening(OrderSuccess::class, ProcessDonationLines::class);
    }

    #[Test]
    public function it_processes_promo_lines_correctly_when_order_success_is_dispatched()
    {
        $this->mock(OrderLineService::class, function (MockInterface $mock) {
            $mock->shouldReceive('processDonation')
                ->once()
                ->withArgs([$this->orderLine]);
        });

        $event = new OrderSuccess($this->order);
        $listener = resolve(ProcessDonationLines::class);
        $listener->handle($event);
    }
}
