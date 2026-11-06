<?php

namespace App\Enums;

enum ProductTypes: string
{
    case TRACK = 'track';
    case DESIGN = 'design';
    case PROMO = 'promo';
    case POINTS = 'points';
    case DONATION = 'donation';
}
