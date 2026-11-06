<?php

namespace App\Jobs;

use App\Facades\Logger;
use App\Models\File;
use App\Services\FileService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private File $file;

    private const TARGET_EXTENSION = 'webp';

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
        $extension = $this->file->getExtension();

        if (! $fileService->isImage($this->file) || empty($this->file->getExtension())) { // Only images will be processed
            return;
        }

        if (! Storage::exists($this->file->path)) {
            $this->handleException(new FileNotFoundException('Image Not Found for Processing. Deleting'));
            $this->file->delete();

            return;
        }

        try {
            $image = $fileService->processImage(Storage::get($this->file->path), self::TARGET_EXTENSION);
        } catch (Exception $exception) {
            $this->handleException(new FileNotFoundException($exception->getMessage()));

            return;
        }

        $oldPath = $this->file->path;
        $newPath = $fileService->getFullPath($this->file->fileable_type, $image, self::TARGET_EXTENSION);

        if ($newPath === $oldPath) {
            return;
        }

        $saved = Storage::put($newPath, $image);

        if ($saved && Storage::exists($newPath)) {
            $this->file->path = $newPath;
            $this->file->saveQuietly();

            Storage::delete($oldPath);
        } else {
            $this->handleException(new FileNotFoundException('Error Processing Image'));

            try {
                $this->file->delete();
            } catch (Exception $e) {
                $this->handleException(new Exception('Error Deleting Image '.$e->getMessage()));
            }
        }
    }

    private function handleException(Exception $exception): void
    {
        Logger::exception($exception, $this->file->toArray());
    }
}
