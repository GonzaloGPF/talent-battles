<?php

namespace App\Interfaces;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasVotesInterface
{
    /**
     * @return MorphMany<Vote, covariant Model>
     */
    public function votes(): MorphMany;
}
