<?php

namespace App\Objects;

use App\Enums\ProductTypes;
use App\Helpers\Calculator;
use App\Models\Product;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;

class CartItem implements Arrayable, JsonSerializable
{
    public ?string $name;

    public ?string $description;

    public ?ProductTypes $productType;

    public ?int $sellerId;

    public ?float $price;

    public ?float $quantity;

    public ?float $discount;

    public ?float $discountAmount;

    public ?float $subtotal;

    public ?float $total;

    public ?float $tax;

    public ?float $taxAmount;

    public ?float $totalWithTaxes;

    public ?string $orderableType;

    public ?int $orderableId;

    public ?int $productId;

    public Product $product;

    private Calculator $calculator;

    public function __construct(Product $product, int $quantity = 1, ?array $itemData = [])
    {
        $this->calculator = new Calculator;

        $this->name = $product->name;
        $this->productId = $product->id;
        $this->productType = $product->product_type;
        $this->sellerId = $product->created_by;
        $this->orderableType = $this->parseOrderableType($itemData['orderable_type'] ?? $product->orderable_type);
        $this->orderableId = $itemData['orderable_id'] ?? $product->orderable_id;
        $this->description = $itemData['description'] ?? $this->getDescription($product, $quantity, $itemData);
        $this->product = $product;

        $totals = $this->calculator->getProductTotals($product, $quantity);

        $this->price = $totals['price'];
        $this->quantity = $totals['quantity'];
        $this->subtotal = $totals['subtotal'];
        $this->discount = $totals['discount'];
        $this->discountAmount = $totals['discount_amount'];
        $this->total = $totals['total'];
        $this->tax = $totals['tax'];
        $this->taxAmount = $totals['tax_amount'];
        $this->totalWithTaxes = $totals['total_with_taxes'];
    }

    public function getKeyString(): string
    {
        return $this->productId.$this->orderableType.$this->orderableId;
    }

    public function toArray(): array
    {
        return [
            'seller_id' => $this->sellerId,
            'product_id' => $this->productId,
            'orderable_id' => $this->orderableId,
            'orderable_type' => $this->orderableType,
            'name' => $this->name,
            'description' => $this->description,
            'product_type' => $this->productType->value,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'discount_amount' => $this->discountAmount,
            'total' => $this->total,
            'tax' => $this->tax,
            'tax_amount' => $this->taxAmount,
            'total_with_taxes' => $this->totalWithTaxes,
        ];
    }

    public function jsonSerialize(): string|bool
    {
        return json_encode($this->toArray());
    }

    public function getDescription(?Product $product = null, ?float $quantity = null, ?array $itemData = []): string
    {
        $product = $product ?? $this->product;
        $quantity = $quantity ?? $this->quantity;

        return match ($product->product_type) {
            ProductTypes::TRACK,
            ProductTypes::DESIGN => trans('help.buy_of', ['description' => $product->name]),
            ProductTypes::POINTS => trans('help.buying_points', ['points' => $quantity]),
            ProductTypes::PROMO => trans('help.featuring_creation', [
                'creation' => morphedModel($itemData['orderable_type'], $itemData['orderable_id'])?->name,
                'date' => today()->addMonths($quantity)->toDateString(),
            ]),
            ProductTypes::DONATION => $this->description ?? modelTitle('donation'),
        };
    }

    private function parseOrderableType(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (Str::startsWith($value, 'App\\Models\\')) {
            return $value;
        }

        return Str::of($value)
            ->ucfirst()
            ->singular()
            ->prepend('App\\Models\\')
            ->value();
    }
}
