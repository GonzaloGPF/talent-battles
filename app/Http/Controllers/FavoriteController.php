<?php

namespace App\Http\Controllers;

use App\Events\FavoriteCreated;
use App\Http\Requests\FavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use App\Services\FavoriteService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Class FavoriteController
 *
 * @group Favorites
 */
class FavoriteController extends Controller
{
    /**
     * FavoriteController constructor.
     */
    public function __construct(public FavoriteService $favoriteService)
    {
        parent::__construct();
        $this->middleware('auth:sanctum')->except('index');
    }

    /**
     * Display the specified resource.
     *
     *
     * @throws AuthorizationException
     */
    public function show(Favorite $favorite): JsonResponse
    {
        if ($favorite->created_by !== auth()->id()) {
            throw new AuthorizationException;
        }

        $this->loadRelations($favorite);

        return $this->showResponse(new FavoriteResource($favorite));
    }

    /**
     * Display a listing of the Favorite.
     */
    public function index(): JsonResponse
    {
        $favorites = Favorite::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(FavoriteResource::collection($favorites));
    }

    /**
     * Store a newly created Favorite in storage.
     */
    public function store(FavoriteRequest $request): JsonResponse
    {
        $favorite = $this->favoriteService->create($request->getModel(), $request->get('favorite_list_id'));

        FavoriteCreated::dispatch($favorite);

        return $this->storeResponse(new FavoriteResource($favorite));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FavoriteRequest $request, Favorite $favorite): JsonResponse
    {
        $favorite->update($request->all());

        return $this->updateResponse(new FavoriteResource($favorite));
    }

    /**
     * Remove the specified Favorite from storage.
     *
     *
     * @throws AuthorizationException
     */
    public function destroy(Favorite $favorite): JsonResponse
    {
        Gate::authorize('is_owner', $favorite);

        $this->destroyModel($favorite);

        return $this->destroyResponse();
    }
}
