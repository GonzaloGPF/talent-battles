<?php

namespace App\Casts;

use App\Models\Product;
use App\Objects\CartItem;
use App\Services\CartService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CartItemCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        return collect(json_decode($value, true))
            ->map(fn (array $itemData) => new CartItem(Product::fromItem($itemData), $itemData['quantity'], $itemData));
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string|false
    {
        return json_encode(resolve(CartService::class)->parseItemsData($value)->toArray());
    }
}
