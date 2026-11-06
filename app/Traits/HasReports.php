<?php

namespace App\Traits;

use App\Models\Report;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReports
{
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
