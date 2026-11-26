<?php

namespace Tests\Feature\Services;

use App\Api\StripeApi;
use App\Enums\PaymentStates;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Transfer;
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
    public function it_transfers_the_seller_part_of_a_sale_to_their_stripe_account()
    {
        $sale = $this->paidSale();

        $this->mock(StripeApi::class, function (MockInterface $mock) use ($sale) {
            $mock->shouldReceive('getChargeId')->once()->with('pi_1')->andReturn('ch_1');
            $mock->shouldReceive('transferToSeller')
                ->once()
                ->with($sale, 'acct_1', 'ch_1')
                ->andReturn(Transfer::constructFrom(['id' => 'tr_1']));
        });

        $this->assertTrue($this->service->payToSeller($sale));
        $this->assertSame('tr_1', $sale->fresh()->transfer_id);
    }

    #[Test]
    public function a_sale_is_never_paid_twice()
    {
        $sale = $this->paidSale(['transfer_id' => 'tr_1']);

        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('transferToSeller');
        });

        $this->assertTrue($this->service->payToSeller($sale));
    }

    #[Test]
    public function a_seller_without_a_stripe_account_is_not_paid()
    {
        $sale = $this->paidSale(account: null);

        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('transferToSeller');
        });

        $this->assertFalse($this->service->payToSeller($sale));
        $this->assertNull($sale->fresh()->transfer_id);
    }

    #[Test]
    public function a_failed_transfer_leaves_the_sale_pending()
    {
        $sale = $this->paidSale();

        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('getChargeId')->andReturn('ch_1');
            $mock->shouldReceive('transferToSeller')->once()->andReturnNull();
        });

        $this->assertFalse($this->service->payToSeller($sale));
        $this->assertNull($sale->fresh()->transfer_id);
    }

    /**
     * A sale of a seller with a Stripe account, from an order paid with the payment intent pi_1.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function paidSale(array $attributes = [], ?array $account = ['id' => 'acct_1']): Sale
    {
        $seller = create(User::class, ['account' => $account]);
        $order = create(Order::class, ['meta' => ['payment_intent_id' => 'pi_1']]);
        $orderLine = create(OrderLine::class, ['order_id' => $order->id]);

        return create(Sale::class, [
            'created_by' => $seller->id,
            'order_line_id' => $orderLine->id,
            ...$attributes,
        ]);
    }
}
