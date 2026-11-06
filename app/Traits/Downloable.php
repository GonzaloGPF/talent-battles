<?php

namespace App\Traits;

use App\Models\Download;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Downloable
{
    public function downloads(): MorphMany
    {
        return $this->morphMany(Download::class, 'downloable');
    }

    public function download(): MorphOne
    {
        return $this->morphOne(Download::class, 'downloable')
            ->where('created_by', auth()->id())
            ->latest();
    }
}
