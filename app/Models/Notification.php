<?php

namespace App\Models;

use App\Scopes\ViewOnlyMine;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @mixin IdeHelperNotification
 */
class Notification extends DatabaseNotification
{
    use HasFactory, IsFilterable;

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ViewOnlyMine(false, 'notifiable_id'));
    }
}
