<?php

namespace App\Services;

use App\Enums\FileTypes;
use App\Facades\Logger;
use App\Helpers\AudioHelper;
use App\Interfaces\HasFilesInterface;
use App\Models\Design;
use App\Models\File;
use App\Models\Track;
use BackedEnum;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Alignment;
use Intervention\Image\ImageManager;

class FileService
{
    public static string $FILES_DIRECTORY = 'files';

    public static int $IMG_WITH = 800;

    public static int $IMG_QUALITY = 90;

    public static array $IMAGE_MIMES = [
        'image/png',
        'image/jpg',
        'image/jpeg',
        'image/avif',
        'image/webp',
    ];

    public static array $AUDIO_MIMES = [
        'audio/mpeg',
        'audio/mp3',
        'audio/mp4',
        'audio/ogg',
        'audio/vnd.wav',
        'audio/wave',
        'audio/wav',
        'audio/x-wav',
        'audio/x-pn-wav',
    ];

    public static array $MIME_EXT = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/avif' => 'avif',
        'image/webp' => 'webp',
        'audio/mpeg' => 'mp3',
        'audio/mp4' => 'mp4',
        'audio/ogg' => 'ogg',
        'audio/vnd.wav' => 'wav',
        'audio/wave' => 'wav',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
        'audio/x-pn-wav' => 'wav',
        'application/pdf' => 'pdf',
        'application/json' => 'json',
        'application/octet-stream' => 'dat',
    ];

    public const WATERMARK_AUDIO = 'watermark.mp3';

    public const WATERMARK_IMAGE = 'watermark.webp';

    private AudioHelper $audioHelper;

    private ImageManager $imageManager;

    public function __construct(AudioHelper $audioHelper, ImageManager $imageManager)
    {
        $this->audioHelper = $audioHelper;
        $this->imageManager = $imageManager;
    }

    private function createFile(HasFilesInterface $model, array $attributes): ?File
    {
        $path = $attributes['path'] ?? null;

        if (! $path || ! Storage::exists($path)) {
            Logger::exception(new Exception('Could not save File in: '.$path), ['file_id' => $model->id]);

            return null;
        }

        $file = (new File($attributes))
            ->forceFill([
                'mime' => $attributes['mime'],
                'fileable_id' => $model->id,
                'fileable_type' => get_class($model),
                'file_type' => $attributes['file_type'] ?? null,
                'path' => $path,
                'secret' => uniqid(),
            ]);

        $file->save();

        return $file;
    }

    /**
     * Saves a file to a given model. The model must implement HasFilesInterface.
     */
    public function saveFile(HasFilesInterface $model, ?UploadedFile $uploadedFile = null, ?FileTypes $fileType = null): ?File
    {
        if (! $uploadedFile) {
            return null;
        }

        $directory = $this->getDirectory(get_class($model));
        $mime = $uploadedFile->getMimeType();

        if ($mime === 'application/octet-stream' || $mime === 'text/plain') { // Last resource... Having problems with .doc and .csv
            $mime = $uploadedFile->getClientMimeType();
        }

        return $this->createFile($model, [
            'name' => $this->getName($uploadedFile),
            'mime' => $mime,
            'path' => $uploadedFile->storeAs($directory, $this->getHashedFilename($uploadedFile)),
            'file_type' => $this->getFileType($uploadedFile, $fileType),
        ]);
    }

    public function saveFiles(HasFilesInterface $model, Collection $uploadedFiles, ?BackedEnum $fileType = null): Collection
    {
        return $uploadedFiles->filter()
            ->map(fn (UploadedFile $uploadedFile) => $this->saveFile($model, $uploadedFile, $fileType))
            ->filter();
    }

    /**
     * Given model deletes its old physic file and updates with a new uploaded file.
     */
    public function updateFile(?File $file, UploadedFile $newFile): ?File
    {
        if (! $file) {
            return null;
        }

        Storage::delete($file->path);

        $directory = $this->getDirectory($file->fileable_type);

        $file->forceFill([
            'name' => $this->getName($newFile),
            'mime' => $newFile->getMimeType(),
            'path' => $newFile->storeAs($directory, $this->getHashedFilename($newFile)),
        ])->save();

        return $file;
    }

    public function updateOrSave(HasFilesInterface $model, UploadedFile $uploadedFile): ?File
    {
        if ($model->file) {
            return $this->updateFile($model->file, $uploadedFile);
        } else {
            return $this->saveFile($model, $uploadedFile);
        }
    }

    private function getName(UploadedFile $uploadedFile): string
    {
        return pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
    }

    public function getDirectory(string $fileableType): string
    {
        $modelClass = str_replace('App\\Models\\', '', $fileableType);
        $modelDirectory = Str::plural(Str::snake($modelClass));

        return implode('/', array_filter([
            self::$FILES_DIRECTORY,
            today()->year,
            $modelDirectory,
        ]));
    }

    public function getFullPath(string $fileableType, string $content, string $extension): string
    {
        $directory = $this->getDirectory($fileableType);

        $hashedFilename = $this->getHashedFilename($content, $extension);

        return "$directory/$hashedFilename";
    }

    /**
     * Process an Image to optimize space, using Intervention package.
     *
     * Parameter $imageData can be one of the following types:
     *
     * string - Path of the image in filesystem.
     * string - Binary image data.
     * string - Data-URL encoded image data.
     * string - Base64 encoded image data.
     * object - SplFileInfo instance (To handle Laravel file uploads via Symfony\Component\HttpFoundation\File\UploadedFile)
     */
    public function processImage(object|string $imageData, string $extension): string
    {
        return $this->imageManager
            ->decode($imageData)
            ->scaleDown(width: self::$IMG_WITH)
            ->encodeUsingFileExtension($extension, quality: self::$IMG_QUALITY)
            ->toString();
    }

    public function getDuration(File $file): float
    {
        return $this->audioHelper->getSeconds($file);
    }

    public function getBPM(File $file): float
    {
        return $this->audioHelper->getBpm($file);
    }

    public function getWaveform(File $file): array|string|null
    {
        try {
            return $this->audioHelper->getWaveformData($file);
        } catch (Exception $exception) {
            Logger::exception($exception, ['file' => $file->toArray()]);

            return null;
        }
    }

    /**
     * Using the mime type, it will detect if its an image or not.
     */
    public function isImage(File $file): bool
    {
        return str_contains($file->mime, 'image');
    }

    /**
     * Using the mime type, it will detect if it's an audio or not.
     */
    public function isAudio(File $file): bool
    {
        return str_contains($file->mime, 'audio');
    }

    /**
     * Returns a hashed base in file's content.
     * It tries to guess the file extension by looking for in file headers, however, sometimes
     * files are not correctly generated, missing its correct header... In these cases, we will trust client's extension
     */
    public function getHashedFilename(string|UploadedFile $uploadedFile, ?string $extension = null): string
    {
        if ($uploadedFile instanceof UploadedFile) {
            $hash = md5_file($uploadedFile);

            $extension = $uploadedFile->guessExtension();

            if (! $extension || $extension === 'bin' || $extension === 'txt') { // Last resource...
                $extension = $uploadedFile->getClientOriginalExtension();
            }
        } else {
            $hash = md5($uploadedFile);
        }

        $hash = md5($hash.uniqid());

        if (! $extension) {
            return $hash;
        }

        return "$hash.$extension";
    }

    public static function copyFile(File $file, array $attributes = []): File
    {
        $salt = uniqid();

        $copy = $file->replicate();
        $copy->secret = uniqid();

        $filename = pathinfo($copy->path, PATHINFO_FILENAME);
        $newPath = str_replace($filename, "{$filename}_$salt", $copy->path);

        Storage::copy($copy->path, $newPath);

        $copy->forceFill(array_merge($attributes, [
            'path' => $newPath,
        ]))->saveQuietly();

        return $copy;
    }

    /**
     * @return string[]
     */
    public static function getFileRules(array $mimeTypes): array
    {
        return ['nullable', 'file', 'max:20000', 'mimetypes:'.implode(',', $mimeTypes)];
    }

    /**
     * @return string[]
     */
    public static function getImageRules(): array
    {
        return self::getFileRules(self::$IMAGE_MIMES);
    }

    /**
     * @return string[]
     */
    public static function getAudioRules(): array
    {
        return self::getFileRules(self::$AUDIO_MIMES);
    }

    public function watermarkTrack(Track $track, File $audio): Track  // TODO: test it
    {
        $taggedFile = $this->audioHelper->generateWatermark($audio);

        if ($taggedFile) {
            $track->audio()->save($taggedFile);
        }

        return $track;
    }

    public function watermarkDesign(Design $model, File $fileToWatermark): Design  // TODO: test it
    {
        $watermarkPath = Storage::disk('public')->path(self::WATERMARK_IMAGE);
        $sourcePath = Storage::path($fileToWatermark->path);
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $destinyPath = str_replace($filename, "{$filename}_tagged", $sourcePath);

        $this->imageManager
            ->decodePath($sourcePath)
            ->insert($watermarkPath, alignment: Alignment::CENTER)
            ->save($destinyPath);

        $taggedFile = $fileToWatermark->replicate();
        $taggedFile->path = str_replace($filename, "{$filename}_tagged", $taggedFile->path);
        $taggedFile->saveQuietly();

        $model->image()->save($taggedFile);

        return $model;
    }

    private function getFileType(UploadedFile $uploadedFile, ?FileTypes $fileType): ?FileTypes
    {
        if ($fileType) {
            return $fileType;
        }

        if (Str::contains($uploadedFile->getMimeType(), 'audio')) {
            return FileTypes::AUDIO;
        }

        if (Str::contains($uploadedFile->getMimeType(), 'image')) {
            return FileTypes::IMAGE;
        }

        return null;
    }
}
