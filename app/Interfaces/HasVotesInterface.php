<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasVotesInterface
{
    public function votes(): MorphMany;
}
