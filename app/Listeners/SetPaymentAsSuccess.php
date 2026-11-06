<?php

namespace App\Listeners;

use App\Events\OrderSuccess;
use App\Services\PaymentService;

class SetPaymentAsSuccess
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
    public function handle(OrderSuccess $event): void
    {
        $this->paymentService->setPaymentAsSuccess($event->order);
    }
}
