<?php

namespace App\Models;

use App\Interfaces\HasCreatedByInterface;
use App\Scopes\ViewOnlyMine;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSale
 */
class Sale extends Model implements HasCreatedByInterface
{
    /** @use HasFactory<SaleFactory> */
    use HasCreatedBy,
        HasFactory,
        IsFilterable;

    protected $casts = [
        'product_id' => 'int',
        'order_line_id' => 'int',
        'amount' => 'float',
        'commission' => 'float',
        'commission_amount' => 'float',
        'total' => 'float',
    ];

    public ?Model $morphedModel = null;

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new ViewOnlyMine);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The order line the sale comes from. Order lines are only visible to their buyer, and the sale belongs to the
     * seller, so its scopes are left out.
     *
     * @return BelongsTo<OrderLine, $this>
     */
    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(OrderLine::class)->withoutGlobalScopes();
    }
}
