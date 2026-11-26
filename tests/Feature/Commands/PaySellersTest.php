<?php

namespace Tests\Feature\Commands;

use App\Models\OrderLine;
use App\Models\Sale;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaySellersTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_pays_the_sales_still_pending_and_says_how_many_it_paid()
    {
        $pending = create(Sale::class, ['order_line_id' => create(OrderLine::class)->id]);
        create(Sale::class, ['order_line_id' => create(OrderLine::class)->id, 'transfer_id' => 'tr_1']);
        create(Sale::class, ['order_line_id' => null]);

        $this->mock(PaymentService::class, function (MockInterface $mock) use ($pending) {
            $mock->shouldReceive('payToSeller')
                ->once()
                ->withArgs(fn (Sale $sale) => $sale->is($pending))
                ->andReturnTrue();
        });

        $this->artisan('app:pay-sellers')
            ->expectsOutput('Paid 1 of 1 pending sales.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_fails_when_a_sale_still_cannot_be_paid()
    {
        create(Sale::class, ['order_line_id' => create(OrderLine::class)->id]);

        $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('payToSeller')->once()->andReturnFalse();
        });

        $this->artisan('app:pay-sellers')
            ->expectsOutput('Paid 0 of 1 pending sales.')
            ->assertFailed();
    }
}
