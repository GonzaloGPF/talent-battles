<?php

namespace App\Listeners;

use App\Events\OrderSuccess;
use App\Models\OrderLine;
use App\Services\OrderLineService;

class ProcessDonationLines
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private readonly OrderLineService $orderLineService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderSuccess $event): void
    {
        $event->order
            ->orderLines
            ->each(fn (OrderLine $orderLine) => $this->orderLineService->processDonation($orderLine));
    }
}
