<?php

namespace App\Listeners;

use App\Events\OrderSuccess;
use App\Events\SalesCreated;
use App\Models\OrderLine;
use App\Services\SaleService;

class CreateSales
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private readonly SaleService $saleService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderSuccess $event): void
    {
        $sales = $event->order->orderLines
            ->filter(fn (OrderLine $orderLine) => $orderLine->isCreation())
            ->map(fn (OrderLine $orderLine) => $this->saleService->createFrom($orderLine));

        SalesCreated::dispatch($sales);
    }
}
