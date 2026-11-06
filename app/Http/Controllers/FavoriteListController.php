<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavoriteListRequest;
use App\Http\Resources\FavoriteListResource;
use App\Models\FavoriteList;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class FavoriteListController extends Controller
{
    /**
     * Display a listing of the FavoriteList.
     */
    public function index(): JsonResponse
    {
        $favoriteLists = FavoriteList::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(FavoriteListResource::collection($favoriteLists));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FavoriteListRequest $request, FavoriteList $favoriteList): JsonResponse
    {
        $favoriteList->update($request->validated());

        return $this->updateResponse(new FavoriteListResource($favoriteList));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FavoriteListRequest $request): JsonResponse
    {
        $favoriteList = FavoriteList::create($request->all());

        return $this->storeResponse(new FavoriteListResource($favoriteList));
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @throws AuthorizationException
     */
    public function destroy(FavoriteList $favoriteList): JsonResponse
    {
        $this->destroyModel($favoriteList);

        return $this->destroyResponse(new FavoriteListResource($favoriteList));
    }
}
