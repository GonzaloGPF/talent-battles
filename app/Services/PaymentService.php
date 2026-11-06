<?php

namespace App\Services;

use App\Api\StripeApi;
use App\Enums\PaymentStates;
use App\Events\OrderSuccess;
use App\Models\Order;
use App\Models\Sale;
use Illuminate\Support\Facades\Cache;

class PaymentService
{
    /**
     * Completes an order if Stripe confirms its payment. This is the only place that can do it: the success page,
     * the webhook and paying with a saved card can happen at once, and the lock stops them from completing it twice.
     */
    public function confirmOrder(Order $order): bool
    {
        $paymentIntentId = $order->meta['payment_intent_id'] ?? null;

        if (! $paymentIntentId) {
            return false;
        }

        return (bool) Cache::lock("payment_intent.$paymentIntentId", 60)->get(function () use ($order, $paymentIntentId) {
            if ($order->refresh()->payment_state === PaymentStates::SUCCESS) {
                return true;
            }

            if ($this->isPaymentIntentUsed($order, $paymentIntentId) || ! resolve(StripeApi::class)->isOrderPaid($order)) {
                return false;
            }

            OrderSuccess::dispatch($order->unsetRelations());

            return true;
        });
    }

    /**
     * Nothing stops an order from being created with the payment intent of another one, which would get it for free.
     */
    private function isPaymentIntentUsed(Order $order, string $paymentIntentId): bool
    {
        return Order::withoutGlobalScopes()
            ->where('meta->payment_intent_id', $paymentIntentId)
            ->where('payment_state', PaymentStates::SUCCESS)
            ->whereKeyNot($order->id)
            ->exists();
    }

    public function setPaymentAsSuccess(Order $order): ?Order
    {
        if ($order->succeeded) {
            return null;
        }

        $order->forceFill([
            'payment_state' => PaymentStates::SUCCESS,
            'succeeded' => now(),
        ])->save();

        return $order;
    }

    public function payToSeller(Sale $sale): void
    {
        resolve(StripeApi::class)->transferMoney(
            $sale->createdBy,
            $sale->total,
            $sale->description
        );
    }
}
