<?php

namespace Orders;

use App\Api\StripeApi;
use App\Enums\PaymentStates;
use App\Events\OrderSuccess;
use App\Http\Middleware\ValidateSignature;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderSuccessTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->user = $this->signIn();
        $this->order = create(Order::class, [
            'created_by' => $this->user->id,
            'payment_state' => PaymentStates::PENDING,
            'email' => $this->user->email,
            'meta' => ['payment_intent_id' => 'pi_1'],
        ]);

        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('isOrderPaid')->once()->andReturn(true);
        });
    }

    #[Test]
    public function it_can_set_as_success_an_order_with_track_order_lines()
    {
        $this->makeRequest()
            ->assertSuccessful();

        Event::assertDispatched(OrderSuccess::class, function (OrderSuccess $event) {
            return $event->order->id === $this->order->id;
        });
    }

    #[Test]
    public function it_can_set_as_success_an_order_with_track_order_lines_from_unauthenticated_user()
    {
        $this->logout();

        $this->order->forceFill([
            'created_by' => null,
            'email' => 'test@mail.com',
        ])->save();

        $this->makeRequest()
            ->assertSuccessful();

        Event::assertDispatched(OrderSuccess::class, function (OrderSuccess $event) {
            return $event->order->id === $this->order->id;
        });
    }

    private function makeRequest(): TestResponse
    {
        $route = route('orders.success', $this->order);

        return $this->withoutMiddleware(ValidateSignature::class)
            ->get($route);
    }
}
