<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasReportsInterface
{
    public function reports(): MorphMany;
}
