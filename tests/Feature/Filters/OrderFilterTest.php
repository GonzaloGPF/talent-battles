<?php

namespace Tests\Feature\Filters;

use App\Enums\PaymentStates;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->signIn();
        create(Order::class, ['created_by' => auth()->id()], 3); // Extra orders
    }

    #[Test]
    public function it_can_filter_orders_by_payment_state()
    {
        Order::query()->update(['payment_state' => PaymentStates::PENDING]);

        create(Order::class, ['payment_state' => PaymentStates::SUCCESS]);

        $data = Order::filter(['payment_states' => [PaymentStates::SUCCESS]])->get();
        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_orders_by_succeeded()
    {
        Order::query()->update(['succeeded' => null]);

        create(Order::class, ['succeeded' => now()]);

        $data = Order::filter(['succeeded' => true])->get();
        $this->assertCount(1, $data);

        $data = Order::filter(['succeeded' => false])->get();
        $this->assertCount(3, $data);
    }
}
