<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackRequest;
use App\Http\Resources\TrackResource;
use App\Models\Track;
use App\Services\BattleService;
use App\Services\FileService;
use App\Services\PointsService;
use App\Services\TrackService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class TrackController extends Controller
{
    protected bool $onlyAction = true;

    /**
     * TrackController constructor.
     */
    public function __construct(
        private readonly TrackService $trackService,
        private readonly PointsService $pointsService,
        private readonly FileService $fileService,
        private readonly BattleService $battleService,
    ) {
        parent::__construct();
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Track $track): JsonResponse
    {
        return $this->showResponse(new TrackResource($track));
    }

    /**
     * Display a listing of the Track.
     */
    public function index(): JsonResponse
    {
        $tracks = Track::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(TrackResource::collection($tracks));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws AuthorizationException
     */
    public function store(TrackRequest $request): JsonResponse
    {
        $this->authorize('create', Track::class);

        $track = $this->trackService->createTrack(
            $request->validated(),
            $this->getFile('audio'),
            $this->getFile('image'),
            $this->getFile('file_to_sell')
        );

        $this->pointsService->earnPoints(config('company.points.awards.upload_creation'));

        if ($request->get('battle')) {
            $this->battleService->createForTrack($track);
            $this->pointsService->spendPoints(config('company.points.prices.create_battle'));
        }

        return $this->storeResponse(new TrackResource($track));
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthorizationException
     */
    public function update(TrackRequest $request, Track $track): JsonResponse
    {
        $this->authorize('update', $track);

        $track->update($request->validated());

        if (! $track->isInSale() && $audio = $this->getFile('audio')) {
            $this->fileService->updateFile($track->audio, $audio);
        }

        if ($image = $this->getFile('image')) {
            if ($track->image) {
                $this->fileService->updateFile($track->image, $image);
            } else {
                $this->fileService->saveFile($track, $image);
            }
        }

        return $this->updateResponse(new TrackResource($track));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws AuthorizationException
     */
    public function destroy(Track $track): JsonResponse
    {
        $this->authorize('delete', $track);

        $this->destroyModel($track);

        return $this->destroyResponse(new TrackResource($track));
    }
}
