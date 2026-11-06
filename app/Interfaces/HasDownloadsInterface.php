<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * @property Collection $downloads
 */
interface HasDownloadsInterface
{
    public function downloads(): MorphMany;
}
