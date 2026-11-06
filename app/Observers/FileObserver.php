<?php

namespace App\Observers;

use App\Facades\Logger;
use App\Jobs\ProcessAudioJob;
use App\Jobs\ProcessImageJob;
use App\Models\File;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\Storage;

class FileObserver
{
    public function saving(File $file): void
    {
        $file->secret = $file->secret ?? uniqid();
    }

    public function saved(File $file): void
    {
        if (empty($file->getExtension())) {
            Logger::exception(new Exception('Unknown File extension '.$file->path), ['file' => $file->toArray()]);

            return;
        }

        if (! Storage::exists($file->path)) {
            Logger::exception(new Exception('File not saved. Deleting... '.$file->path), ['file' => $file->toArray()]);
            try {
                $file->delete();
            } catch (Exception $exception) {
            }

            return;
        }

        if (in_array($file->mime, FileService::$IMAGE_MIMES)) {
            ProcessImageJob::dispatch($file);
        }

        if (in_array($file->mime, FileService::$AUDIO_MIMES)) {
            ProcessAudioJob::dispatch($file);
        }
    }

    public function deleting(File $file): void
    {
        // Delete associated saved file
        Storage::delete($file->path);
    }
}
