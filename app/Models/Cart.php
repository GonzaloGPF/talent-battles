<?php

namespace App\Models;

use App\Casts\CartItemCast;
use App\Interfaces\HasCreatedByInterface;
use App\Objects\CartItem;
use App\Scopes\MineOrGuestScope;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCart
 */
class Cart extends Model implements HasCreatedByInterface
{
    use HasCreatedBy,
        HasFactory;

    protected $fillable = ['items'];

    protected $casts = [
        'items' => CartItemCast::class,
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new MineOrGuestScope);
    }

    /**
     * @return $this
     */
    public function cleanItems(): static
    {
        $this->update(['items' => []]);

        return $this;
    }

    public function getTotal(): float
    {
        return $this->items
            ->map(fn (CartItem $cartItem) => $cartItem->totalWithTaxes)
            ->sum();
    }
}
