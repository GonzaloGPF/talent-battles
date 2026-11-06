<?php

namespace App\Helpers;

use App\Enums\ProductTypes;
use App\Models\Product;
use App\Services\CompanyService;

class Calculator
{
    const TAX_21 = 21;

    public static function getTax(): float
    {
        return 0; // self::TAX_21; // TODO: review this
    }

    public static function getOrderTotals(float $brute, ?float $discountAmount = 0, ?float $taxAmount = 0): array
    {
        $subtotal = $brute - $discountAmount;

        if ($taxAmount) {
            $tax = $subtotal / $taxAmount * 100;
        } else {
            $tax = 0;
        }

        $total = $subtotal + $taxAmount;

        if ($brute) {
            $discount = $discountAmount / $brute * 100;
        } else {
            $discount = 0;
        }

        return [
            'brute' => $brute,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'tax' => $tax, // TODO: maybe we should remove this percentage, it's not real
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }

    public static function getOrderLineTotals(float $price, float $quantity = 1, ?float $discount = 0, ?float $tax = 0): array
    {
        $subtotal = $price * $quantity;
        $discountAmount = $subtotal * ($discount / 100);
        $total = $subtotal - $discountAmount;
        $taxAmount = $total * ($tax / 100);
        $totalWithTaxes = $total + $taxAmount;

        return [
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'tax' => $tax,
            'tax_amount' => $taxAmount,
            'total_with_taxes' => $totalWithTaxes,
        ];
    }

    public function getProductTotals(Product $product, int $quantity = 1): array
    {
        $pointDiscount = $this->getPointDiscount($quantity);
        $promoDiscount = $this->getPromoDiscount($quantity);

        return match ($product->product_type) {
            ProductTypes::TRACK,
            ProductTypes::DESIGN => $this->getOrderLineTotals($product->price),
            ProductTypes::POINTS => $this->getOrderLineTotals($product->price, $quantity, $pointDiscount, Calculator::TAX_21),
            ProductTypes::PROMO => $this->getOrderLineTotals($product->price, $quantity, $promoDiscount, Calculator::TAX_21),
            ProductTypes::DONATION => $this->getOrderLineTotals($product->price, $quantity),
        };
    }

    public function getPromoDiscount(float $months): float
    {
        $promoData = resolve(CompanyService::class)->getServices()['promo'] ?? [];

        $minMonths = $promoData['min_months_discount'];
        $maxMonths = $promoData['max_months_discount'];
        $maxDiscount = $promoData['max_discount'];
        $discount = $promoData['discount'];

        if ($months <= $minMonths) {
            return 0;
        }

        if ($months >= $maxMonths) {
            return $maxDiscount;
        }

        return ($months - $minMonths) * $discount;
    }

    public function getPointDiscount(int $points): float
    {
        $pointsData = resolve(CompanyService::class)->getBuy();

        $discounts = $pointsData['points']['discounts'] ?? [];

        return collect($discounts)
            ->filter(fn (float $discount, $minPoints) => $points <= $minPoints)
            ->first();
    }
}
