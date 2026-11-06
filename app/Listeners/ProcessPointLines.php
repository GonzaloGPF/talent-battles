<?php

namespace App\Listeners;

use App\Events\OrderSuccess;
use App\Services\OrderLineService;

class ProcessPointLines
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
        $this->orderLineService->processPoints($event->order->orderLines, $event->order->createdBy);
    }
}
