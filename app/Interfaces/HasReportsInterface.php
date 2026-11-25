<?php

namespace App\Interfaces;

use App\Models\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasReportsInterface
{
    /**
     * @return MorphMany<Report, covariant Model>
     */
    public function reports(): MorphMany;
}
