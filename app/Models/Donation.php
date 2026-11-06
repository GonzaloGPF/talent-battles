<?php

namespace App\Models;

use App\Enums\ActivityTypes;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\StripeLineInterface;
use App\Objects\StripeLineItem;
use App\Services\ActivityService;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperDonation
 */
class Donation extends Model implements HasCreatedByInterface, StripeLineInterface
{
    use HasCreatedBy,
        HasFactory,
        IsFilterable;

    protected $casts = [
        'amount' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (Donation $donation) {
            if (! $donation->created_by) {
                return;
            }

            $donation->createdBy
                ->forceFill(['last_donation' => now()])
                ->save();

            resolve(ActivityService::class)->create(ActivityTypes::DO_DONATION, $donation);
        });
    }

    public function toLineItem(): StripeLineItem
    {
        return new StripeLineItem($this->message, $this->amount);
    }
}
