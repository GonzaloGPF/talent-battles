<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Http\Resources\FileResource;
use App\Interfaces\HasFilesInterface;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class FileController
 *
 * @group Files
 */
class FileController extends Controller
{
    /**
     * Display the specified Files.
     *
     * @throws BindingResolutionException|FileNotFoundException|AuthorizationException
     */
    public function show(File $file): Response
    {
        $this->authorize('view', $file);

        return $this->fileResponse($file);
    }

    /**
     * Display the specified File.
     */
    public function index(): JsonResponse
    {
        $files = File::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(FileResource::collection($files));
    }

    /**
     * Store a newly created File in storage.
     */
    public function store(FileRequest $request): JsonResponse
    {
        /** @var HasFilesInterface $model */
        $model = morphedModel($request->get('model_type'), $request->get('model_id'));

        $files = $this->saveFilesTo($model, 'files', $request->get('file_type'));

        return $this->storeResponse(FileResource::collection($files));
    }

    /**
     * Update the specified File in storage.
     *
     * @throws AuthorizationException
     */
    public function update(FileRequest $request, File $file, FileService $fileService): JsonResponse
    {
        $this->authorize('update', $file);

        if (! $this->getFile()) {
            return $this->emptyResponse();
        }

        $fileService->updateFile($file, $this->getFile());

        return $this->updateResponse(new FileResource($file));
    }

    /**
     * Remove the specified File from storage.
     *
     * @throws AuthorizationException
     */
    public function destroy(File $file, FileService $fileService): JsonResponse
    {
        $this->authorize('update', $file);

        if ($fileService->isImage($file)) {
            $this->modelName = 'image';
            $this->female = true;
        }

        $this->destroyModel($file);

        return $this->destroyResponse();
    }

    /**
     * Download multiple files
     *
     * @throws BindingResolutionException
     */
    public function zip(): Response
    {
        $data = request()->validate([
            'file_ids' => 'required|array|min:1',
            'file_ids.*' => 'exists:files,id',
        ]);

        return $this->zipResponse($data['file_ids']);
    }
}
