<?php

namespace App\Models;

use App\Enums\ProductTypes;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasFilesInterface;
use App\Traits\HasCreatedBy;
use App\Traits\HasFiles;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperProduct
 */
class Product extends Model implements HasCreatedByInterface, HasFilesInterface
{
    use HasCreatedBy,
        HasFactory,
        HasFiles,
        IsFilterable,
        SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'exclusive',
    ];

    protected $casts = [
        'created_by' => 'int',
        'product_type' => ProductTypes::class,
        'price' => 'float',
        'exclusive' => 'bool',
        'sales' => 'int',
        'last_sale' => 'datetime',
        'promoted' => 'datetime',
    ];

    public static function fromItem(array $itemData): Product
    {
        return (new Product)->forceFill([
            'id' => $itemData['product_id'] ?? null,
            'orderable_type' => $itemData['orderable_type'] ?? null,
            'orderable_id' => $itemData['orderable_id'] ?? null,
            'created_by' => $itemData['seller_id'] ?? null,
            'product_type' => $itemData['product_type'] ?? null,
            'name' => $itemData['name'] ?? null,
            'description' => $itemData['description'] ?? null,
            'price' => $itemData['price'] ?? null,
            'exclusive' => $itemData['exclusive'] ?? null,
            'sales' => $itemData['sales'] ?? null,
            'last_sale' => $itemData['last_sale'] ?? null,
            'promoted' => $itemData['promoted'] ?? null,
        ]);
    }

    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isBlocked(): bool
    {
        return $this->exclusive && $this->last_sale;
    }
}
