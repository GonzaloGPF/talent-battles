<?php

namespace App\Jobs;

use App\Facades\Logger;
use App\Models\File;
use App\Models\Track;
use App\Services\FileService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private File $file;

    /**
     * Create a new job instance.
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(FileService $fileService): void
    {
        if (! $fileService->isAudio($this->file)) { // Only audios will be processed
            return;
        }

        if (! Storage::exists($this->file->path)) {
            Logger::exception(new FileNotFoundException('Audio Not Found for Processing. Deleting'), $this->file->toArray());
            $this->file->delete();

            return;
        }

        if ($this->file->fileable_type === Track::class) {
            /** @var Track $track */
            $track = $this->file->fileable;

            $track->forceFill([
                'waveform' => $fileService->getWaveform($this->file),
                'duration' => $fileService->getDuration($this->file),
                'bpm' => $track->bpm ?? $fileService->getBPM($this->file),
            ])->save();
        }
    }
}
