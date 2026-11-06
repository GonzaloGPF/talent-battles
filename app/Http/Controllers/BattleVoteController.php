<?php

namespace App\Http\Controllers;

use App\Http\Requests\BattleVoteRequest;
use App\Http\Resources\VoteResource;
use App\Models\Battle;
use App\Services\PointsService;
use App\Services\VoteService;
use Illuminate\Http\JsonResponse;

class BattleVoteController extends Controller
{
    protected bool $onlyAction = true;

    private float $votePoints;

    public function __construct(private readonly VoteService $voteService, private readonly PointsService $pointsService)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->votePoints = config('company.points.awards.do_vote');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BattleVoteRequest $request, Battle $battle): JsonResponse
    {
        $vote = $this->voteService->create($battle->id, $request->getModel());

        if (! $vote) {
            return $this->emptyResponse();
        }

        $this->pointsService->earnPoints($this->votePoints);

        return $this->storeResponse(new VoteResource($vote), 'vote_sent');
    }
}
