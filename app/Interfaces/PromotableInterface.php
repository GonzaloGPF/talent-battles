<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface PromotableInterface
{
    public function scopePromoted(Builder $builder): Builder;
}
