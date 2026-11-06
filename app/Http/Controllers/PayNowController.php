<?php

namespace App\Http\Controllers;

use App\Api\StripeApi;
use App\Http\Requests\PayNowRequest;
use App\Http\Resources\OrderResource;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;

class PayNowController extends Controller
{
    protected string $modelName = 'order';

    private ?User $user;

    public function __construct(private readonly OrderService $orderService, private readonly StripeApi $stripeApi, private readonly PaymentService $paymentService)
    {
        parent::__construct();
        $this->middleware('auth:sanctum');
        $this->user = auth()->user();
    }

    public function store(PayNowRequest $request): JsonResponse
    {
        $cart = $this->user->cart;

        $payment = $this->stripeApi->createConfirmedPaymentIntent($cart->getTotal());

        if (! $payment) {
            return $this->emptyResponse();
        }

        try {
            $payment->validate();
        } catch (Exception $exception) {
            return $this->emptyResponse($exception->getMessage());
        }

        $order = $this->orderService->createFromCart($cart, $this->user->email, [
            'payment_intent_id' => $this->stripeApi->getPaymentIntentId($payment),
        ]);

        if (! $order) {
            return $this->emptyResponse();
        }

        if (! $this->paymentService->confirmOrder($order)) {
            return $this->emptyResponse();
        }

        return $this->storeResponse((new OrderResource($order))
            ->additional([
                'secret' => $payment->clientSecret(),
            ]));
    }
}
