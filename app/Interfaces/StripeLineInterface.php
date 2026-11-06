<?php

namespace App\Interfaces;

use App\Objects\StripeLineItem;

interface StripeLineInterface
{
    public function toLineItem(): StripeLineItem;
}
