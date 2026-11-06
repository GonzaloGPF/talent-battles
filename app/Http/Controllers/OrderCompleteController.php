<?php

namespace App\Http\Controllers;

use App\Api\StripeApi;
use App\Enums\PaymentStates;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Stripe\Exception\ApiErrorException;

class OrderCompleteController extends Controller
{
    protected string $modelName = 'order';

    public function __construct(private readonly StripeApi $stripeApi)
    {
        parent::__construct();
        $this->middleware('auth:sanctum');
    }

    /**
     * @throws AuthorizationException
     * @throws ApiErrorException
     */
    public function show(Order $order): JsonResponse
    {
        if (in_array($order->payment_state, [PaymentStates::SUCCESS, PaymentStates::ERROR])) {
            throw new AuthorizationException;
        }

        if (empty($order->meta['payment_intent_id'])) {
            return $this->emptyResponse();
        }

        $paymentIntent = $this->stripeApi->confirmPaymentIntent($order->meta['payment_intent_id'], URL::signedRoute('orders.success', compact('order')));

        if (! $paymentIntent) {
            return $this->emptyResponse();
        }

        return $this->showResponse(new OrderResource($order));
    }
}
