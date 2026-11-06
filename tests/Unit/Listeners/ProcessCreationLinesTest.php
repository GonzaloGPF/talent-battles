<?php

namespace Listeners;

use App\Enums\ProductTypes;
use App\Events\OrderSuccess;
use App\Listeners\ProcessCreationLines;
use App\Models\Order;
use App\Models\OrderLine;
use App\Services\OrderLineService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessCreationLinesTest extends TestCase
{
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = new Order;
        $orderLine1 = (new OrderLine)->forceFill([
            'product_type' => ProductTypes::TRACK,
        ]);
        $orderLine2 = (new OrderLine)->forceFill([
            'product_type' => ProductTypes::DESIGN,
        ]);
        $this->order->setRelation('orderLines', collect([
            $orderLine1,
            $orderLine2,
        ]));
    }

    #[Test]
    public function it_listens_to_order_success()
    {
        Event::fake();
        Event::assertListening(OrderSuccess::class, ProcessCreationLines::class);
    }

    #[Test]
    public function it_processes_creation_lines_when_order_success_is_dispatched()
    {
        $this->mock(OrderLineService::class, function (MockInterface $mock) {
            $mock->shouldReceive('processCreation')
                ->twice();
        });

        $event = new OrderSuccess($this->order);
        $listener = resolve(ProcessCreationLines::class);
        $listener->handle($event);
    }
}
