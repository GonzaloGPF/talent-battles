<?php

namespace App\Models;

use App\Enums\PaymentStates;
use App\Enums\ProductTypes;
use App\Helpers\Calculator;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasFilesInterface;
use App\Interfaces\StripeLineInterface;
use App\Objects\StripeLineItem;
use App\Scopes\ViewOnlyMine;
use App\Traits\HasCreatedBy;
use App\Traits\HasFiles;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperOrderLine
 */
class OrderLine extends Model implements HasCreatedByInterface, HasFilesInterface, StripeLineInterface
{
    use HasCreatedBy,
        HasFactory,
        HasFiles,
        IsFilterable;

    protected $casts = [
        'created_by' => 'int',
        'seller_id' => 'int',
        'creation_id' => 'int',
        'order_id' => 'int',
        'product_id' => 'int',
        'payment_state' => PaymentStates::class,
        'product_type' => ProductTypes::class,
        'price' => 'float',
        'quantity' => 'float',
        'subtotal' => 'float',
        'discount' => 'float',
        'discount_amount' => 'float',
        'total' => 'float',
        'tax' => 'float',
        'tax_amount' => 'float',
        'total_with_taxes' => 'float',
    ];

    public static array $CREATION_TYPES = [
        ProductTypes::TRACK,
        ProductTypes::DESIGN,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new ViewOnlyMine);

        static::saving(function (OrderLine $orderLine) {
            $totals = Calculator::getOrderLineTotals(
                $orderLine->price,
                $orderLine->quantity,
                $orderLine->discount,
                $orderLine->tax
            );
            $orderLine->forceFill($totals);
        });
    }

    public function toLineItem(): StripeLineItem
    {
        return new StripeLineItem($this->description, $this->total_with_taxes);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function isCreation(): bool
    {
        return in_array($this->product_type, self::$CREATION_TYPES);
    }

    public function isPromo(): bool
    {
        return $this->product_type === ProductTypes::PROMO;
    }

    public function isPoint(): bool
    {
        return $this->product_type === ProductTypes::POINTS;
    }

    public function isDonation(): bool
    {
        return $this->product_type === ProductTypes::DONATION;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderable(): MorphTo
    {
        return $this->morphTo('orderable');
    }

    public function getFileUrl(): ?string
    {
        if (! $this->file) {
            return null;
        }

        return route('web.files.show', [
            'file' => $this->file->id,
            'secret' => $this->file->secret,
        ]);
    }
}
