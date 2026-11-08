<?php

namespace App\Http\Controllers;

use App\Enums\BattleStates;
use App\Http\Requests\BattleRequest;
use App\Http\Resources\BattleResource;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use App\Services\BattleService;
use App\Services\PointsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class BattleController extends Controller
{
    protected bool $female = true;

    private float $battlePoints;

    public function __construct(
        private readonly BattleService $battleService,
        private readonly PointsService $pointsService
    ) {
        parent::__construct();
        $this->middleware('auth:sanctum')->only(['store', 'destroy']);
        $this->battlePoints = config('company.points.prices.create_battle');
    }

    /**
     * Display the specified resource.
     */
    public function show(Battle $battle): JsonResponse
    {
        $this->battleService->refreshBattlesAfterResponse();

        return $this->showResponse(new BattleResource($battle));
    }

    /**
     * Display a listing of the Battle.
     */
    public function index(): JsonResponse
    {
        $this->battleService->refreshBattlesAfterResponse();

        $battles = Battle::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(BattleResource::collection($battles));
    }

    public function store(BattleRequest $request): JsonResponse
    {
        if ($request->get('type') === 'track') {
            $battle = $this->createTrackBattle($request->model, $request->opponent);
        }

        if ($request->get('type') === 'design') {
            $battle = $this->createDesignBattle($request->model, $request->opponent);
        }

        if (empty($battle)) {
            return $this->emptyResponse();
        }

        $this->pointsService->spendPoints($this->battlePoints);

        return $this->storeResponse(new BattleResource($battle));
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @throws AuthorizationException
     */
    public function destroy(Battle $battle): JsonResponse
    {
        if ($battle->battle_state !== BattleStates::OPEN->value) {
            throw new AuthorizationException(trans('validation.wrong_state'));
        }

        $isNotMyBattle = $battle->getCreations()
            ->where('created_by', auth()->id())
            ->isEmpty();

        if ($isNotMyBattle) {
            throw new AuthorizationException;
        }

        $this->destroyModel($battle);

        return $this->destroyResponse(new BattleResource($battle));
    }

    private function createTrackBattle(Track $track, ?Track $opponent): Battle
    {
        if ($opponent) {
            return $this->battleService->createForTracks($track, $opponent);
        } else {
            return $this->battleService->createForTrack($track);
        }
    }

    private function createDesignBattle(Design $design, ?Design $opponent): Battle
    {
        if ($opponent) {
            return $this->battleService->createForDesigns($design, $opponent);
        } else {
            return $this->battleService->createForDesign($design);
        }
    }
}
