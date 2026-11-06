<?php

namespace Tests\Feature\Services;

use App\Api\StripeApi;
use App\Enums\PaymentStates;
use App\Models\Order;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(PaymentService::class);
    }

    #[Test]
    public function it_can_set_payment_as_success()
    {
        /** @var Order $order */
        $order = create(Order::class, [
            'payment_state' => PaymentStates::PENDING,
            'succeeded' => null,
        ]);

        $order = $this->service->setPaymentAsSuccess($order);

        $this->assertEquals(PaymentStates::SUCCESS, $order->payment_state);
        $this->assertNotNull($order->succeeded);
    }

    #[Test]
    public function it_wont_set_payment_as_succeed_if_its_already_success()
    {
        /** @var Order $order */
        $order = create(Order::class, [
            'payment_state' => PaymentStates::SUCCESS,
            'succeeded' => now(),
        ]);

        $order = $this->service->setPaymentAsSuccess($order);

        $this->assertNull($order);
    }

    #[Test]
    public function it_can_pay_to_seller()
    {
        /** @var User $seller */
        $seller = create(User::class);
        /** @var Sale $sale */
        $sale = create(Sale::class, ['created_by' => $seller->id]);
        $sale->setRelation('createdBy', $seller);

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($sale, $seller) {
            $mock->shouldReceive('transferMoney')
                ->once()
                ->with($seller, $sale->total, $sale->description);
        });

        $this->service->payToSeller($sale);
    }
}
