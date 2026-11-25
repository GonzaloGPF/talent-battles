<?php

namespace App\Interfaces;

use App\Models\Download;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * @property Collection<int, Download> $downloads
 */
interface HasDownloadsInterface
{
    /**
     * @return MorphMany<Download, covariant Model>
     */
    public function downloads(): MorphMany;
}
