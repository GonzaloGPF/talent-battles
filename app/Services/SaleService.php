<?php

namespace App\Services;

use App\Models\OrderLine;
use App\Models\Sale;

class SaleService
{
    private float $creationCommission;

    public function __construct()
    {
        $this->creationCommission = config('company.fees.creation');
    }

    public function createFrom(OrderLine $orderLine): ?Sale
    {
        if (! $orderLine->isCreation()) {
            return null;
        }

        $sale = new Sale;
        $commissionAmount = $orderLine->total_with_taxes * ($this->creationCommission / 100);

        $sale->forceFill([
            'created_by' => $orderLine->seller_id,
            'product_id' => $orderLine->product_id,
            'description' => trans('help.you_sold_product', ['product' => $orderLine->product?->name]),
            'amount' => $orderLine->total_with_taxes,
            'commission' => $this->creationCommission,
            'commission_amount' => $commissionAmount,
            'total' => $orderLine->total_with_taxes - $commissionAmount,
        ])->save();

        return $sale;
    }
}
