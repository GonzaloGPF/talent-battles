<?php

namespace App\Jobs;

use App\Enums\PaymentStates;
use App\Facades\Logger;
use App\Models\Order;
use App\Services\PaymentService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandlePaymentIntent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public array $payload, public string $paymentIntentEvent)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(PaymentService $paymentService): void
    {
        try {
            $paymentIntentId = data_get($this->payload, 'data.object.id');

            // Webhooks have no user or session, so the scopes of Order would hide every order
            $order = Order::withoutGlobalScopes()
                ->where('meta->payment_intent_id', $paymentIntentId)
                ->latest('id')
                ->first();

            if (! $order) {
                return;
            }

            if ($this->paymentIntentEvent === 'payment_intent.succeeded') {
                $paymentService->confirmOrder($order);
            }

            if ($this->paymentIntentEvent === 'payment_intent.payment_failed' && $order->payment_state !== PaymentStates::SUCCESS) {
                $order->forceFill([
                    'payment_state' => PaymentStates::ERROR,
                ])->save();
            }
        } catch (Exception $exception) {
            Logger::exception($exception);
        }
    }
}
