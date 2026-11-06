<?php

namespace App\Interfaces;

use App\Models\File;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface HasFilesInterface
 *
 * @property ?File $audio
 * @property ?File $image
 * @property ?File $file
 * @property int $id
 */
interface HasFilesInterface
{
    public function files(): MorphMany;

    /**
     *@see HasFileTrait
     * @see HasFiles
     */
    public function getFilesDirectory(): string;

    /**
     * @return void
     */
    public function deleteFiles();
}
