<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class StripeHelper
{
    public function containsPaymentMethod(Collection $paymentMethods, string $paymentMethodId): bool
    {
        $stripePaymentMethods = $paymentMethods->toArray();

        return collect($stripePaymentMethods)
            ->contains('id', $paymentMethodId);
    }
}
