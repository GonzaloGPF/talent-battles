<?php

namespace App\Http\Controllers;

use App\Http\Requests\DesignRequest;
use App\Http\Resources\DesignResource;
use App\Models\Design;
use App\Services\BattleService;
use App\Services\DesignService;
use App\Services\FileService;
use App\Services\PointsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class DesignController extends Controller
{
    protected bool $onlyAction = true;

    /**
     * DesignController constructor.
     */
    public function __construct(
        private readonly DesignService $designService,
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
    public function show(Design $design): JsonResponse
    {
        return $this->showResponse(new DesignResource($design));
    }

    /**
     * Display a listing of the Design.
     */
    public function index(): JsonResponse
    {
        $designs = Design::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(DesignResource::collection($designs));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws AuthorizationException
     */
    public function store(DesignRequest $request): JsonResponse
    {
        $this->authorize('create', Design::class);

        $design = $this->designService->createDesign(
            $request->validated(),
            $this->getFile('image'),
            $this->getFile('file_to_sell')
        );

        $this->pointsService->earnPoints(config('company.points.awards.upload_creation'));

        if ($request->get('battle')) {
            $this->battleService->createForDesign($design);
            $this->pointsService->spendPoints(config('company.points.prices.create_battle'));
        }

        return $this->storeResponse(new DesignResource($design));
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthorizationException
     */
    public function update(DesignRequest $request, Design $design): JsonResponse
    {
        $this->authorize('update', $design);

        $design->update($request->validated());

        if (! $design->isInSale() && $file = $this->getFile('image')) {
            $this->fileService->updateFile($design->image, $file);
        }

        return $this->updateResponse(new DesignResource($design));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws AuthorizationException
     */
    public function destroy(Design $design): JsonResponse
    {
        $this->authorize('delete', $design);

        $this->destroyModel($design);

        return $this->destroyResponse(new DesignResource($design));
    }
}
