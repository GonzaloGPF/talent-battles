<?php

namespace App\Services;

use App\Helpers\Calculator;
use App\Models\Cart;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\User;
use App\Objects\CartItem;
use Illuminate\Support\Collection;

class OrderLineService
{
    public function __construct(private readonly Calculator $calculator) {}

    public function makeFromCart(Cart $cart): Collection
    {
        return $cart->items
            ->map(fn (CartItem $cartItem) => $this->makeFromCartItem($cartItem))
            ->filter();
    }

    private function makeFromCartItem(CartItem $cartItem): OrderLine
    {
        $product = $cartItem->product;

        $totals = $this->calculator->getProductTotals($product, $cartItem->quantity);

        return (new OrderLine)
            ->forceFill(array_merge([
                'created_by' => auth()->id(),
                'description' => $cartItem->description,
                'product_type' => $product->product_type,
                'product_id' => $product->id,
                'seller_id' => $product->created_by,
                'orderable_type' => $product->orderable_type ?? $cartItem->orderableType,
                'orderable_id' => $product->orderable_id ?? $cartItem->orderableId,
            ], $totals));
    }

    public function processCreation(OrderLine $orderLine): void
    {
        if (! $orderLine->isCreation()) {
            return;
        }

        $product = $orderLine->product;

        if (! $product) {
            return;
        }

        $file = $product->file;

        if (! $file) {
            return;
        }

        $product->forceFill([
            'last_sale' => now()->toDateTimeString(),
            'sales' => $product->sales + 1,
            //            'price' => 0,
        ])->saveQuietly();

        resolve(FileService::class)->copyFile($file, [
            'created_by' => $orderLine->created_by,
            'fileable_type' => OrderLine::class,
            'fileable_id' => $orderLine->id,
        ]);
    }

    public function processPromo(OrderLine $orderLine): void
    {
        if (! $orderLine->isPromo()) {
            return;
        }

        /** @var Product $product */
        $product = $orderLine->orderable;

        if (! $product) {
            return;
        }

        $product->forceFill([
            'promoted' => now()->addMonths((int) $orderLine->quantity),
        ])->save();
    }

    public function processPoints(Collection $orderLines, ?User $user = null): void
    {
        if (! $user) {
            return;
        }

        $points = $orderLines
            ->filter(fn (OrderLine $orderLine) => $orderLine->isPoint())
            ->map(fn (OrderLine $orderLine) => $orderLine->quantity)
            ->sum() ?? 0;

        resolve(PointsService::class)->earnPoints($points, $user, true);
    }

    public function processDonation(OrderLine $orderLine): void
    {
        if (! $orderLine->isDonation()) {
            return;
        }

        resolve(DonationService::class)->create(
            $orderLine->total_with_taxes,
            $orderLine->description,
            $orderLine->created_by
        );
    }
}
