<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

/**
 * @property Collection $comments
 */
interface HasCommentsInterface
{
    public function comments(): MorphMany|HasMany;

    public function comment(): MorphOne|BelongsTo;
}
