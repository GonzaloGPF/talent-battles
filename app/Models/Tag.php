<?php

namespace App\Models;

use App\Services\TagService;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTag
 */
class Tag extends Model
{
    use HasFactory,
        IsFilterable;

    protected $fillable = [
        'name',
    ];

    protected function name(): Attribute
    {
        return Attribute::set(fn ($value) => TagService::sanitize($value));
    }
}
