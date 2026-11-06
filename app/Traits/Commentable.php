<?php

namespace App\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Commentable
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function comment(): MorphOne
    {
        return $this->morphOne(Comment::class, 'commentable')
            ->where('created_by', auth()->id())
            ->latest();
    }
}
