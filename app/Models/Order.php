<?php

namespace App\Models;

use App\Enums\PaymentStates;
use App\Interfaces\HasCreatedByInterface;
use App\Scopes\MineOrGuestScope;
use App\Traits\HasAddress;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperOrder
 */
class Order extends Model implements HasCreatedByInterface
{
    use HasAddress,
        HasCreatedBy,
        HasFactory,
        IsFilterable,
        SoftDeletes;

    protected $casts = [
        'payment_state' => PaymentStates::class,
        'succeeded' => 'datetime',
        'brute' => 'float',
        'discount' => 'float',
        'discount_amount' => 'float',
        'subtotal' => 'float',
        'tax' => 'float',
        'tax_amount' => 'float',
        'total' => 'float',
        'address' => 'array',
        'meta' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new MineOrGuestScope);
        static::creating(function (HasCreatedByInterface $model) {
            $model->payment_state = $model->payment_state ?? PaymentStates::PENDING;
        });
        static::created(function (Order $order) {
            /** @var User $user */
            $user = auth()->user();
            $user?->cart?->cleanItems();
        });
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class)->withoutGlobalScopes();
    }
}
