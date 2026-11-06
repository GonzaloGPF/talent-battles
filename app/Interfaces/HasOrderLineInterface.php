<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasOrderLineInterface
{
    public function orderLine(): MorphOne;

    public function isBoughtByMe(): bool;
}
