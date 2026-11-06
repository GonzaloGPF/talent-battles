<?php

namespace App\Listeners;

use App\Jobs\HandlePaymentIntent;
use App\Jobs\Stripe\HandleAccountUpdated;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Events\WebhookReceived;

class HandleStripeWebhook
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        $stripeEvent = $event->payload['type'] ?? null;

        if (! $stripeEvent) {
            return;
        }

        DB::table('webhook_calls')->insert([
            'name' => "StripeWebhook:$stripeEvent",
            'payload' => json_encode($event->payload),
            'exception' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($stripeEvent === 'account.updated') { // TODO: check about customer.updated
            HandleAccountUpdated::dispatch($event->payload);
        }

        if (in_array($stripeEvent, ['payment_intent.succeeded', 'payment_intent.payment_failed', 'payment_intent.processing', 'payment_intent.requires_action'])) {
            HandlePaymentIntent::dispatch($event->payload, $stripeEvent);
        }
    }
}
