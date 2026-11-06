<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Objects\CartItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CartService
{
    private static string $cartSessionKey = 'cart_session_key';

    public function getOrCreateCart(): Cart
    {
        $cart = Cart::firstOrCreate();

        if (auth()->guest()) {
            $cart->forceFill(['guest_id' => self::getOrCreateGuestId()])
                ->save();
        }

        return $cart;
    }

    public function addItems(Cart $cart, ?array $itemData = []): Cart
    {
        $oldItems = $cart->items;

        $cart->items = $itemData;

        $cart->update([
            'items' => $cart->items->merge($oldItems),
        ]);

        return $cart;
    }

    public function removeItems(Cart $cart, array $productIds): Cart
    {
        $productIds = collect($productIds)
            ->filter();

        $items = $cart->items
            ->filter(fn (CartItem $cartItem) => ! $productIds->contains(fn (int $productId) => $productId === $cartItem->productId))
            ->toArray();

        $cart->update([
            'items' => $items,
        ]);

        return $cart;
    }

    public static function getOrCreateGuestId(): ?string
    {
        if (auth()->check()) {
            return null;
        }

        $guestId = self::getGuestId();

        if ($guestId) {
            return $guestId;
        }

        $guestId = uniqid();

        session()->put(self::$cartSessionKey, $guestId);

        return $guestId;
    }

    public static function getGuestId(): ?string
    {
        return session(self::$cartSessionKey);
    }

    public function parseItemsData(null|array|Collection $itemsData = []): Collection
    {
        if ($itemsData instanceof Collection) {
            $itemsData = $itemsData->toArray();
        }

        $productIds = Arr::pluck($itemsData ?? [], 'product_id');
        $products = Product::with(['orderable'])->find($productIds);

        return collect($itemsData)
            ->map(function (array $itemData) use ($products) {
                if (empty($itemData['product_id'])) {
                    return null;
                }

                $product = $products->first(fn (Product $product) => $product->id === $itemData['product_id']);

                if (! $product) {
                    return null;
                }

                return new CartItem($product, $itemData['quantity'] ?? 1, $itemData);
            })
            ->filter()
            ->unique(fn (CartItem $cartItem) => $cartItem->getKeyString());
    }
}
