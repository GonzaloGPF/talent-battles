<?php

namespace App\Services;

use App\Api\StripeApi;
use App\Enums\PaymentStates;
use App\Events\OrderSuccess;
use App\Facades\Logger;
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
     * Marks an order as failed if Stripe has its payment as failed: the webhook that reports it could be forged. An order
     * completed in the meantime, by a retried payment for example, is never undone, and the lock is the one of confirmOrder.
     */
    public function failOrder(Order $order): bool
    {
        $paymentIntentId = $order->meta['payment_intent_id'] ?? null;

        if (! $paymentIntentId) {
            return false;
        }

        return (bool) Cache::lock("payment_intent.$paymentIntentId", 60)->get(function () use ($order, $paymentIntentId) {
            if ($order->refresh()->payment_state === PaymentStates::SUCCESS || ! resolve(StripeApi::class)->hasPaymentFailed($paymentIntentId)) {
                return false;
            }

            $order->forceFill(['payment_state' => PaymentStates::ERROR])->save();

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

    /**
     * Transfers the seller's part of a sale to their Stripe account, from the charge that paid the order. A sale
     * keeps its transfer, so it is never paid twice; one that could not be paid stays pending for app:pay-sellers.
     */
    public function payToSeller(Sale $sale): bool
    {
        if ($sale->transfer_id) {
            return true;
        }

        $accountId = $sale->createdBy?->account['id'] ?? null;
        $paymentIntentId = Order::withoutGlobalScopes()->find($sale->orderLine?->order_id)?->meta['payment_intent_id'] ?? null;

        if (! $accountId || ! $paymentIntentId) {
            Logger::error('A sale cannot be paid to its seller', [
                'sale' => $sale->id,
                'account' => $accountId,
                'payment_intent' => $paymentIntentId,
            ]);

            return false;
        }

        $stripeApi = resolve(StripeApi::class);
        $chargeId = $stripeApi->getChargeId($paymentIntentId);
        $transfer = $chargeId ? $stripeApi->transferToSeller($sale, $accountId, $chargeId) : null;

        if (! $transfer) {
            return false;
        }

        $sale->forceFill(['transfer_id' => $transfer->id])->save();

        return true;
    }
}
