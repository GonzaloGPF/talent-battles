<?php

namespace App\Services;

use App\Enums\ActivityTypes;
use App\Jobs\HandleActivity;
use App\Models\Track;
use Illuminate\Http\UploadedFile;

class TrackService extends CreationService
{
    public function createTrack(array $data, UploadedFile $audio, ?UploadedFile $image = null, ?UploadedFile $untaggedFile = null): Track
    {
        $track = Track::create($data);

        if ($image) {
            $this->fileService->saveFile($track, $image);
        }

        if (empty($data['sell'])) {
            $this->fileService->saveFile($track, $audio);
        } else {
            $this->processFileAndProduct($track, $data, $audio, $untaggedFile);
        }

        HandleActivity::dispatch(ActivityTypes::NEW_TRACK, $track);

        return $track;
    }
}
