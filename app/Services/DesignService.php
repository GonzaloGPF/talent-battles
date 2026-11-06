<?php

namespace App\Services;

use App\Enums\ActivityTypes;
use App\Jobs\HandleActivity;
use App\Models\Design;
use Illuminate\Http\UploadedFile;

class DesignService extends CreationService
{
    public function createDesign(array $data, UploadedFile $image, ?UploadedFile $untaggedFile = null): Design
    {
        $fileService = resolve(FileService::class);
        $design = Design::create($data);

        if (empty($data['sell'])) {
            $fileService->saveFile($design, $image);
        } else {
            $this->processFileAndProduct($design, $data, $image, $untaggedFile);
        }

        HandleActivity::dispatch(ActivityTypes::NEW_DESIGN, $design);

        return $design;
    }
}
