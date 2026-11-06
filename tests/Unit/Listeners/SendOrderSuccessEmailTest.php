<?php

namespace Listeners;

use App\Events\OrderSuccess;
use App\Listeners\SendOrderSuccessEmail;
use App\Models\Order;
use App\Services\EmailService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendOrderSuccessEmailTest extends TestCase
{
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = new Order;
    }

    #[Test]
    public function it_listens_to_order_success()
    {
        Event::fake();
        Event::assertListening(OrderSuccess::class, SendOrderSuccessEmail::class);
    }

    #[Test]
    public function it_sends_order_success_email_when_order_success_is_dispatched()
    {
        $this->mock(EmailService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createOrderSuccess')
                ->once()
                ->withArgs([$this->order]);
        });

        $event = new OrderSuccess($this->order);
        $listener = resolve(SendOrderSuccessEmail::class);
        $listener->handle($event);
    }
}
