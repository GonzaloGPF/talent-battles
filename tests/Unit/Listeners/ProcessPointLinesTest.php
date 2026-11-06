<?php

namespace Listeners;

use App\Enums\ProductTypes;
use App\Events\OrderSuccess;
use App\Listeners\ProcessPointLines;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\User;
use App\Services\OrderLineService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessPointLinesTest extends TestCase
{
    private User $user;

    private Order $order;

    private OrderLine $orderLine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User;
        $this->order = new Order;
        $this->orderLine = (new OrderLine)->forceFill([
            'product_type' => ProductTypes::POINTS,
        ]);
        $this->order->setRelations([
            'createdBy' => $this->user,
            'orderLines' => collect([$this->orderLine]),
        ]);
    }

    #[Test]
    public function it_listens_to_order_success()
    {
        Event::fake();
        Event::assertListening(OrderSuccess::class, ProcessPointLines::class);
    }

    #[Test]
    public function it_processes_point_lines_when_order_success_is_dispatched()
    {
        $this->mock(OrderLineService::class, function (MockInterface $mock) {
            $mock->shouldReceive('processPoints')
                ->once()
                ->withArgs([$this->order->orderLines, $this->user]);
        });

        $event = new OrderSuccess($this->order);
        $listener = resolve(ProcessPointLines::class);
        $listener->handle($event);
    }
}
