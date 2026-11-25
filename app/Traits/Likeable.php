<?php

namespace App\Traits;

use App\Models\Like;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Likeable
{
    /**
     * @return MorphMany<Like, $this>
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * @return MorphOne<Like, $this>
     */
    public function like(): MorphOne
    {
        return $this->morphOne(Like::class, 'likeable')
            ->where('created_by', auth()->id());
    }
}
