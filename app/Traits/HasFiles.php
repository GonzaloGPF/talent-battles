<?php

namespace App\Traits;

use App\Enums\FileTypes;
use App\Interfaces\HasFilesInterface;
use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

trait HasFiles
{
    public static function bootHasFiles(): void
    {
        static::deleted(function (HasFilesInterface $model) {
            $model->deleteFiles();
        });
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function images(): MorphMany
    {
        return $this->files()->areImage();
    }

    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable')
            ->latest();
    }

    public function image(): MorphOne
    {
        return $this->file()
            ->where('file_type', FileTypes::IMAGE);
    }

    public function audio(): MorphOne
    {
        return $this->file()
            ->where('file_type', FileTypes::AUDIO);
    }

    /**
     * Each model can define a directory where saving its Files.
     * By default, it will be calculated by model's class name.
     */
    public function getFilesDirectory(): string
    {
        return Str::plural(Str::snake(class_basename($this)));
    }

    public function deleteFiles(): void
    {
        $this->files->each->delete();
    }
}
