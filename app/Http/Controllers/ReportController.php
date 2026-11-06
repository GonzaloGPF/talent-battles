<?php

namespace App\Http\Controllers;

use App\Enums\ReportStates;
use App\Enums\Roles;
use App\Events\ReportClosed;
use App\Events\ReportCreated;
use App\Http\Requests\ReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    protected bool $female = true;

    public function __construct(public ReportService $reportService)
    {
        parent::__construct();
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report): JsonResponse
    {
        $this->loadRelations($report);

        return $this->showResponse(new ReportResource($report));
    }

    /**
     * Display a listing of the Report.
     */
    public function index(): JsonResponse
    {
        $reports = Report::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(ReportResource::collection($reports));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReportRequest $request): JsonResponse
    {
        $report = $this->reportService->create($request->get('message'), $request->getModel());

        $this->saveFilesTo($report);

        ReportCreated::dispatch($report);

        return $this->storeResponse(new ReportResource($report));
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthorizationException
     */
    public function update(Report $report): JsonResponse
    {
        if ($report->created_by !== auth()->id() || $report->report_state !== ReportStates::OPEN) {
            throw new AuthorizationException;
        }

        request()->validate(['message' => 'required|string']);

        $report->update(['message' => request('message')]);

        return $this->updateResponse(new ReportResource($report));
    }

    /**
     * @throws AuthorizationException
     */
    public function solve(Report $report): JsonResponse
    {
        if (! hasRole(Roles::ADMIN) || $report->isFinished()) {
            throw new AuthorizationException;
        }

        request()->validate([
            'resolution' => 'required|string|max:255',
            'closed' => 'nullable|boolean',
        ]);

        $report = $this->reportService->finish($report, request('resolution'), request('closed'));

        ReportClosed::dispatch($report);

        return $this->updateResponse(new ReportResource($report));
    }
}
