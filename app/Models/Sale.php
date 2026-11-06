<?php

namespace App\Models;

use App\Interfaces\HasCreatedByInterface;
use App\Scopes\ViewOnlyMine;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSale
 */
class Sale extends Model implements HasCreatedByInterface
{
    use HasCreatedBy,
        HasFactory,
        IsFilterable;

    protected $casts = [
        'product_id' => 'int',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
