<?php

namespace App\Listeners;

use App\Events\SalesCreated;
use App\Models\Sale;
use App\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;

class PayToSeller implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private readonly PaymentService $paymentService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SalesCreated $event): void
    {
        $event->sales
            ->each(fn (Sale $sale) => $this->paymentService->payToSeller($sale));
    }
}
