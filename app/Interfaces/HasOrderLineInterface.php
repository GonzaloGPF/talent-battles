<?php

namespace App\Interfaces;

use App\Models\OrderLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasOrderLineInterface
{
    /**
     * @return MorphOne<OrderLine, covariant Model>
     */
    public function orderLine(): MorphOne;

    public function isBoughtByMe(): bool;
}
