<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Laravel\Cashier\PaymentMethod;

class StripeHelper
{
    /**
     * @param  Collection<int, PaymentMethod>  $paymentMethods
     */
    public function containsPaymentMethod(Collection $paymentMethods, string $paymentMethodId): bool
    {
        $stripePaymentMethods = $paymentMethods->toArray();

        return collect($stripePaymentMethods)
            ->contains('id', $paymentMethodId);
    }
}
