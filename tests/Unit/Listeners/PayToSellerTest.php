<?php

namespace Tests\Unit\Listeners;

use App\Events\SalesCreated;
use App\Listeners\PayToSeller;
use App\Models\Sale;
use App\Services\PaymentService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PayToSellerTest extends TestCase
{
    private Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sale = new Sale;
    }

    #[Test]
    public function it_listens_to_sale_created()
    {
        Event::fake();
        Event::assertListening(SalesCreated::class, PayToSeller::class);
    }

    #[Test]
    public function it_pays_to_seller_when_sales_created_is_dispatched()
    {
        $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('payToSeller')
                ->once()
                ->with($this->sale);
        });
        $event = new SalesCreated(collect([$this->sale]));
        $listener = resolve(PayToSeller::class);
        $listener->handle($event);
    }
}
