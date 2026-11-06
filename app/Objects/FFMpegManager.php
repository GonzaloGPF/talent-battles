<?php

namespace App\Objects;

use App\Services\FileService;
use Exception;
use FFMpeg\Format\Audio\Mp3;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Media;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class FFMpegManager
{
    public function watermarkAudio(string $filePath): ?string
    {
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $newPath = Str::replace($filename, "{$filename}_tagged", $filePath);
        $watermarkDuration = FFMpeg::fromDisk('public')
            ->open(FileService::WATERMARK_AUDIO)
            ->getDurationInSeconds();

        $audioDuration = FFMpeg::open($filePath)
            ->getDurationInSeconds();

        $loopDuration = $audioDuration - $watermarkDuration;
        $padDuration = $watermarkDuration * 5;
        $tagDelay = '3s|3s'; // delaying each channel

        try {
            FFMpeg::open($filePath)
                ->fromDisk('public')
                ->open(FileService::WATERMARK_AUDIO)
                ->export()
                ->inFormat(new Mp3)
                ->setAdditionalParameters([
                    '-filter_complex',
                    "[0:a]volume=volume=2[aout0];[1:a]apad=pad_dur=$padDuration,volume=volume=1.5[aout1];[aout1]aloop=loop=-1:size=2e+09,adelay=$tagDelay,atrim=start=0:end=$watermarkDuration:duration={$loopDuration}[aconcat];[aout0][aconcat]amix=inputs=2:duration=longest:dropout_transition=4 [aout]",
                ])
                ->addFormatOutputMapping(new Mp3, Media::make(Storage::getDefaultDriver(), $newPath), ['[aout]'])
                ->save();
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }

        return $newPath;
    }
}
