<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Promotable
{
    public function initializeFeaturable(): void
    {
        $this->casts += ['promoted' => 'datetime'];
    }

    public function scopePromoted(Builder $builder): Builder
    {
        return $builder->where('promoted', '<=', today()->endOfDay());
    }
}
