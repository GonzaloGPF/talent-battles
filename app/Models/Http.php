<?php

namespace App\Models;

use App\Interfaces\HasCreatedByInterface;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperHttp
 */
class Http extends Model implements HasCreatedByInterface
{
    use HasCreatedBy;

    protected $casts = [
        'data' => 'array',
        'outgoing' => 'bool',
    ];
}
