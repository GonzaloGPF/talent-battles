<?php

namespace App\Helpers;

use App\Facades\Logger;
use App\Models\File;
use App\Objects\FFMpegManager;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Symfony\Component\Process\Process;

class AudioHelper
{
    public const FORMAT_DATA = 'dat';

    public const FORMAT_JSON = 'json';

    public const FORMAT_PNG = 'png';

    private Process $process;

    public string $output;

    public function __construct(private readonly FFMpegManager $FFMpegManager) {}

    public function generateWatermark(File $file): ?File
    {
        $watermarkPath = $this->FFMpegManager->watermarkAudio($file->path);

        if (! $watermarkPath) {
            return null;
        }

        $taggedFile = $file->replicate();
        $taggedFile->path = $watermarkPath;
        $taggedFile->saveQuietly();

        return $taggedFile;
    }

    /**
     * It generates a file with waveform data.
     * After generating the file, it will read its content in $waveformData to return it.
     * Finally, it will delete generated file
     */
    public function getWaveformData(File $file, string $format = self::FORMAT_JSON): array|string|null
    {
        $filename = pathinfo($file->path, PATHINFO_FILENAME);
        $extension = pathinfo($file->path, PATHINFO_EXTENSION);
        $tmpFilePath = "$filename.$extension";

        $fullPath = $this->createTempFile($file->path);

        $directory = pathinfo($fullPath, PATHINFO_DIRNAME);
        $destiny = "$directory/$filename.$format";

        $this->process = resolve(Process::class, [
            'command' => ['audiowaveform', '-i', $fullPath, '-o', $destiny, '-b', '8', '-z', '8192'],
        ]);

        try {
            $this->process->run();

            $this->output = $this->getProcessOutput();

            $jsonFilePath = Str::replace(".$extension", ".$format", $tmpFilePath);

            if (! Storage::disk('tmp')->exists($jsonFilePath)) {
                Logger::error("File not found $jsonFilePath", ['output' => $this->output]);

                return null;
            }

            $waveformData = Storage::disk('tmp')->get($jsonFilePath);

            Storage::disk('tmp')->delete($jsonFilePath);
            Storage::disk('tmp')->delete($tmpFilePath);

            if ($format === self::FORMAT_JSON) {
                return (array) json_decode($waveformData);
            }

            return $waveformData;
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    public function getSeconds(File $file): float
    {
        if (! Storage::exists($file->path)) {
            return 0;
        }

        try {
            return FFMpeg::open($file->path)->getDurationInSeconds();
        } catch (Exception $exception) {
            Logger::exception($exception, ['file' => $file->toArray()]);

            return 0;
        }
    }

    public function getBpm(File $file): ?float
    {
        $fullPath = $this->createTempFile($file->path);
        $isWav = in_array(pathinfo($fullPath, PATHINFO_EXTENSION), ['wav', 'wave']);

        if ($isWav) {
            $command = ['soundstretch', $fullPath, '-bpm'];
        } else {
            $command = ['bpm-tag', '-n', '-f', $fullPath];
        }

        $this->process = resolve(Process::class, ['command' => $command]);

        try {
            $this->process->run();

            $this->output = $this->getProcessOutput();

            return $this->extractBPM($isWav, $this->output);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    /**
     * Creates a temporal file and returns its full path
     */
    private function createTempFile(string $path): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $tmpFilePath = "$filename.$extension";

        Storage::disk('tmp')
            ->put($tmpFilePath, Storage::get($path));

        return Storage::disk('tmp')->path($tmpFilePath);
    }

    private function getProcessOutput(): string
    {
        $this->output = $this->process->getOutput();

        if (empty($this->output)) {
            $this->output = $this->process->getErrorOutput();
        }

        return $this->output;
    }

    private function extractBPM(bool $isWav, string $output): float
    {
        if ($isWav) {
            preg_match('/BPM rate (.*)\n/', $output, $matches);

            $bpm = $matches[1] ?? null;
        } else {
            $pieces = explode(' ', $output);

            $bpm = $pieces[1] ?? null;
        }

        return round(floatval($bpm));
    }
}
