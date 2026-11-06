<?php

namespace App\Objects;

use Illuminate\Contracts\Support\Arrayable;
use JetBrains\PhpStorm\ArrayShape;

class StripeLineItem implements Arrayable
{
    public function __construct(
        public string $name,
        public float $amount = 0.0,
        public float $quantity = 1.0,
        public ?string $currency = null
    ) {
        $this->currency = $currency ?? config('company.currency');
    }

    #[ArrayShape(['price_data' => 'array', 'quantity' => 'float'])]
    public function toArray(): array
    {
        return [
            'price_data' => [
                'currency' => $this->currency,
                'product_data' => [
                    'name' => $this->name,
                    //                    'description' => $this->description,
                ],
                'unit_amount' => $this->amount * 100,
                //                'tax_behavior' => 'inclusive', // inclusive, exclusive, or unspecified
            ],
            'quantity' => $this->quantity,
        ];
    }
}
