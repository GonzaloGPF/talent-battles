<?php

namespace App\Http\Controllers\Stripe;

use App\Api\StripeApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Stripe\PaymentIntent;

class PaymentIntentController extends Controller
{
    protected bool $onlyAction = true;

    public function __construct(private readonly StripeApi $stripeApi)
    {
        parent::__construct();
    }

    public function store(): JsonResponse
    {
        request()->validate([
            'amount' => 'required|numeric|min:0',
            'email' => 'required|email',
        ]);

        $payment = $this->stripeApi->createPaymentIntent(request('amount'), request('email'));

        if (! $payment) {
            return $this->emptyResponse();
        }

        return $this->dataResponse($this->buildMessage('created'), [
            'client_secret' => $payment->clientSecret(),
            //            'payment_intent_id'=> $payment->id, // Not allowed, client must fetch paymentId with stripe.js using client_secret
        ]);
    }

    public function update(PaymentIntent $paymentIntent): JsonResponse
    {
        request()->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $paymentIntent = $this->stripeApi->updatePaymentIntent($paymentIntent, request('amount'));

        return $this->dataResponse($this->buildMessage('updated'), [
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }
}
