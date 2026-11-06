<?php

namespace App\Jobs;

use App\Models\Design;
use App\Models\File;
use App\Models\Track;
use App\Services\FileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWatermark implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Track|Design $creation, private readonly File $file)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(FileService $fileService): void
    {
        if ($this->creation instanceof Track) {
            $fileService->watermarkTrack($this->creation, $this->file);
        }

        if ($this->creation instanceof Design) {
            $fileService->watermarkDesign($this->creation, $this->file);
        }
    }
}
