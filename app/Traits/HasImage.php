<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasImage
{
    public function initializeHasImage(): void
    {
        $this->appends[] = 'image_url';
    }

    /**
     * Update image.
     */
    public function updateImage(UploadedFile $photo): void
    {
        tap($this->image, function ($previous) use ($photo) {
            $this->forceFill([
                'image' => $photo->storePublicly(
                    $this->getImageDirectory(),
                    ['disk' => $this->imageDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->imageDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete the images.
     */
    public function deleteImage(): void
    {
        Storage::disk($this->imageDisk())->delete($this->image);

        $this->forceFill([
            'image' => null,
        ])->save();
    }

    /**
     * Get the URL to the image.
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? Storage::disk($this->imageDisk())->url($this->image)
            : $this->defaultImageUrl();
    }

    /**
     * Get the default image if no image has been uploaded.
     */
    protected function defaultImageUrl(): string
    {
        return url('images/logo_mini.php');
    }

    /**
     * Get the disk that images should be stored on.
     */
    protected function imageDisk(): string
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : 'public';
    }

    protected function getImageDirectory(): string
    {
        $modelDirectory = Str::plural(Str::snake(class_basename($this)));

        return implode('/', array_filter([
            'images',
            $modelDirectory,
        ]));
    }
}
