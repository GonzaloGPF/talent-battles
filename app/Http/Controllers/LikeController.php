<?php

namespace App\Http\Controllers;

use App\Events\LikeCreated;
use App\Http\Requests\LikeRequest;
use App\Http\Resources\LikeResource;
use App\Models\Like;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Class LikeController
 *
 * @group Likes
 */
class LikeController extends Controller
{
    /**
     * LikeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:sanctum')->except('index');
    }

    /**
     * Display a listing of the Like.
     */
    public function index(): JsonResponse
    {
        $likes = Like::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(LikeResource::collection($likes));
    }

    /**
     * Store a newly created Like in storage.
     */
    public function store(LikeRequest $request): JsonResponse
    {
        /** @var Like $like */
        $like = (new Like)
            ->likeable()
            ->associate($request->getModel());

        $like->save();

        LikeCreated::dispatch($like);

        return $this->storeResponse(new LikeResource($like));
    }

    /**
     * Remove the specified Like from storage.
     *
     *
     * @throws AuthorizationException
     */
    public function destroy(Like $like): JsonResponse
    {
        Gate::authorize('is_owner', $like);

        $this->destroyModel($like);

        return $this->destroyResponse();
    }
}
