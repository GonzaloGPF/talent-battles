<?php

namespace App\Traits;

use App\Models\Like;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Likeable
{
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function like(): MorphOne
    {
        return $this->morphOne(Like::class, 'likeable')
            ->where('created_by', auth()->id());
    }
}
