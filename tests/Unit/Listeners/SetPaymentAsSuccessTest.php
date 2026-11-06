<?php

namespace Listeners;

use App\Enums\PaymentStates;
use App\Events\OrderSuccess;
use App\Listeners\SetPaymentAsSuccess;
use App\Models\Order;
use App\Services\PaymentService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SetPaymentAsSuccessTest extends TestCase
{
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = (new Order)->forceFill([
            'payment_state' => PaymentStates::PENDING,
            'succeeded' => null,
        ]);
    }

    #[Test]
    public function it_listen_to_order_success()
    {
        Event::fake();
        Event::assertListening(OrderSuccess::class, SetPaymentAsSuccess::class);
    }

    #[Test]
    public function it_can_set_payment_as_success_when_order_success_is_dispatched()
    {
        $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('setPaymentAsSuccess')
                ->once()
                ->andReturn($this->order);
        });

        $event = new OrderSuccess($this->order);

        $listener = resolve(SetPaymentAsSuccess::class);
        $listener->handle($event);
    }
}
