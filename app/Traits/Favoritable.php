<?php

namespace App\Traits;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Favoritable
{
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function favorite(): MorphOne
    {
        return $this->morphOne(Favorite::class, 'favoritable')
            ->where('created_by', auth()->id())
            ->latest();
    }
}
