<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlbumRequest;
use App\Http\Resources\AlbumResource;
use App\Models\Album;
use App\Services\FileService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class AlbumController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * Display a listing of the Album.
     */
    public function index(): JsonResponse
    {
        $albums = Album::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(AlbumResource::collection($albums));
    }

    /**
     * Display the specified resource.
     */
    public function show(Album $album): JsonResponse
    {
        $this->loadRelations($album);

        return $this->showResponse(new AlbumResource($album));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AlbumRequest $request): JsonResponse
    {
        $album = Album::create($request->validated());

        $this->saveFileTo($album, 'image');

        return $this->storeResponse(new AlbumResource($album));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AlbumRequest $request, Album $album, FileService $fileService): JsonResponse
    {
        $album->update($request->validated());

        if ($image = $this->getFile('image')) {
            if ($album->image) {
                $fileService->updateFile($album->image, $image);
            } else {
                $fileService->saveFile($album, $image);
            }
        }

        return $this->updateResponse(new AlbumResource($album));
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @throws AuthorizationException
     */
    public function destroy(AlbumRequest $request, Album $album): JsonResponse
    {
        $this->destroyModel($album);

        return $this->destroyResponse(new AlbumResource($album));
    }
}
