<?php

namespace App\Models;

use App\Enums\EmailTypes;
use App\Interfaces\HasCreatedByInterface;
use App\Scopes\EmailScope;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperEmail
 */
class Email extends Model implements HasCreatedByInterface
{
    use HasCreatedBy,
        HasFactory,
        IsFilterable;

    protected $casts = [
        'email_type' => EmailTypes::class,
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'sent' => 'datetime',
        'meta' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new EmailScope);
    }
}
