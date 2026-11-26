<?php

namespace App\Enums;

enum ProductTypes: string
{
    case TRACK = 'track';
    case DESIGN = 'design';
    case PROMO = 'promo';
    case POINTS = 'points';
    case DONATION = 'donation';

    /**
     * Delivered, or started, as soon as it is paid. Everything but a donation, which is not a purchase.
     */
    public function isDeliveredAtOnce(): bool
    {
        return $this !== self::DONATION;
    }
}
