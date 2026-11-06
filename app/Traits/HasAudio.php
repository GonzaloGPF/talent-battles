<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasAudio
{
    public function initializeHasAudio(): void
    {
        $this->appends[] = 'audio_url';
    }

    /**
     * Update audio.
     */
    public function updateAudio(UploadedFile $photo): void
    {
        tap($this->audio, function ($previous) use ($photo) {
            $this->forceFill([
                'audio' => $photo->storePublicly(
                    $this->getImageDirectory(),
                    ['disk' => $this->audioDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->audioDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete the audios.
     */
    public function deleteAudio(): void
    {
        Storage::disk($this->audioDisk())->delete($this->audio);

        $this->forceFill([
            'audio' => null,
        ])->save();
    }

    /**
     * Get the URL to the audio.
     */
    public function getAudioUrlAttribute(): string
    {
        return $this->audio
            ? Storage::disk($this->audioDisk())->url($this->audio)
            : $this->defaultAudioUrl();
    }

    /**
     * Get the default audio if no audio has been uploaded.
     */
    protected function defaultAudioUrl(): string
    {
        return url('audios/logo_mini.php');
    }

    /**
     * Get the disk that audios should be stored on.
     */
    protected function audioDisk(): string
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : 'public';
    }

    protected function getAudioDirectory(): string
    {
        $modelDirectory = Str::plural(Str::snake(class_basename($this)));

        return implode('/', array_filter([
            'audios',
            $modelDirectory,
        ]));
    }
}
