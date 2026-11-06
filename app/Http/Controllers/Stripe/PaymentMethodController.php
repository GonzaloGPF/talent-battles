<?php

namespace App\Http\Controllers\Stripe;

use App\Api\StripeApi;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodController extends Controller
{
    protected bool $onlyAction = true;

    private ?User $user;

    public function __construct(private readonly StripeApi $stripeApi)
    {
        parent::__construct();
        $this->middleware('auth:sanctum');
        $this->user = auth()->user();
    }

    public function index(): JsonResponse
    {
        return $this->indexResponse(JsonResource::collection($this->stripeApi->getPaymentMethods()));
    }

    public function store(): JsonResponse
    {
        request()->validate(['payment_method_id' => 'required|string']);

        $this->stripeApi->createOrGetStripeCustomer($this->user);

        $paymentMethod = $this->stripeApi->addPaymentMethod(request('payment_method_id'), $this->user);

        if (! $this->user->hasDefaultPaymentMethod()) {
            $this->stripeApi->setDefaultPaymentMethod($paymentMethod->id);
        }

        return $this->storeResponse(JsonResource::collection($this->stripeApi->getPaymentMethods()));
    }

    public function destroy(string $paymentMethodId): JsonResponse
    {
        $this->stripeApi->deletePaymentMethod($paymentMethodId);

        return $this->destroyResponse(JsonResource::collection($this->stripeApi->getPaymentMethods()));
    }
}
