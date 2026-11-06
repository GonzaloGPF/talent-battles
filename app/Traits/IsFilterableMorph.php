<?php

namespace App\Traits;

use App\Filters\Filter;

trait IsFilterableMorph
{
    use IsFilterable;

    public function modelFilter(): string
    {
        return Filter::class;
    }
}
