<?php

namespace App\Responses;

use App\Models\File;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FileResponse
{
    private ZipArchive $zipArchive;

    public function __construct(ZipArchive $zipArchive)
    {
        $this->zipArchive = $zipArchive;
    }

    /**
     * Force browser to download given file
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function file(File $file, bool $stream = false): Response
    {
        $this->cleanBuffer();

        if (! Storage::exists($file->path)) {
            throw new FileNotFoundException;
        }

        $content = Storage::get($file->path);
        $fileSize = Storage::size($file->path);
        $shortenFileSize = $fileSize - 1;
        $name = $file->fullName();
        $disposition = $stream
            ? 'inline;'
            : 'attachment;';

        return response()
            ->make($content, Response::HTTP_OK, [
                'Accept-Ranges' => 'bytes',
                'Content-Range' => "bytes 0-$shortenFileSize/$fileSize",
                'Content-Length' => $fileSize,
                'Content-Type' => $file->mime,
                'Content-Disposition' => "$disposition filename=$name",
            ]);
    }

    /**
     * Force browser to download file zip
     *
     * @throws BindingResolutionException
     */
    public function zip(array $fileIds): Response
    {
        $this->cleanBuffer();
        $localDisk = Storage::disk('local');

        if (! $localDisk->exists(File::PATH_TMP)) {
            $localDisk->makeDirectory(File::PATH_TMP);
        }

        $extension = '.zip';
        $pathTmp = File::PATH_TMP.'/'.uniqid().$extension;

        try {
            $this->zipArchive->open($localDisk->path($pathTmp), ZipArchive::CREATE);

            File::find($fileIds)
                ->each(fn (File $file) => $this->zipArchive->addFromString($file->fullName(), Storage::get($file->path)));

            $this->zipArchive->close();

            $response = response()
                ->make($localDisk->get($pathTmp), Response::HTTP_OK, [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename='.modelTitle('file', true).$extension,
                ]);

            $localDisk->delete($pathTmp);

            return $response;
        } catch (Exception $exception) {
            $this->zipArchive->close();
            $localDisk->delete($pathTmp);
            throw $exception;
        }
    }

    /**
     * Prepares buffer
     */
    private function cleanBuffer()
    {
        if (ob_get_length()) {
            ob_end_clean();
            ob_start();
        }
    }
}
