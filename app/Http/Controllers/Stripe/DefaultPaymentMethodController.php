<?php

namespace App\Http\Controllers\Stripe;

use App\Api\StripeApi;
use App\Helpers\StripeHelper;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class DefaultPaymentMethodController extends Controller
{
    protected bool $onlyAction = true;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:sanctum');
    }

    /**
     * Display the specified resource.
     */
    public function show(StripeApi $stripeApi): JsonResponse
    {
        $paymentMethod = $stripeApi->getDefaultPaymentMethod();

        if (! $paymentMethod) {
            return $this->emptyResponse();
        }

        return $this->showResponse(new JsonResource($paymentMethod));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(string $id, StripeApi $stripeApi, StripeHelper $helper): JsonResponse
    {
        if (! $helper->containsPaymentMethod($stripeApi->getPaymentMethods(), $id)) {
            throw new AuthorizationException;
        }

        $paymentMethod = $stripeApi->setDefaultPaymentMethod($id);

        if (! $paymentMethod) {
            return $this->emptyResponse();
        }

        return $this->updateResponse(new JsonResource($paymentMethod));
    }
}
