<?php

namespace App\Traits;

use App\Enums\ProductTypes;

/**
 * What a buyer accepts before paying: the terms of service and, when the cart has something delivered as soon as it
 * is paid, that immediate delivery, which gives up the right of withdrawal for points and downloads. The request
 * holds the cart items in "items".
 */
trait ValidatesCheckoutConsents
{
    /**
     * @return array<string, array<int, string>>
     */
    protected function consentRules(): array
    {
        return [
            'terms_and_conditions' => ['accepted'],
            'immediate_delivery' => $this->hasItemsDeliveredAtOnce() ? ['accepted'] : [],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function consentMessages(): array
    {
        return [
            'terms_and_conditions.accepted' => trans('help.must_accept_terms_and_conditions'),
            'immediate_delivery.accepted' => trans('help.must_accept_immediate_delivery'),
        ];
    }

    /**
     * The consents the order keeps as proof, to confirm them in the order email.
     *
     * @return array{terms_accepted: bool, immediate_delivery: bool}
     */
    public function consents(): array
    {
        return [
            'terms_accepted' => $this->boolean('terms_and_conditions'),
            'immediate_delivery' => $this->hasItemsDeliveredAtOnce() && $this->boolean('immediate_delivery'),
        ];
    }

    private function hasItemsDeliveredAtOnce(): bool
    {
        return $this->collect('items')
            ->contains(fn (array $item) => (bool) ProductTypes::tryFrom($item['product_type'] ?? '')?->isDeliveredAtOnce());
    }
}
