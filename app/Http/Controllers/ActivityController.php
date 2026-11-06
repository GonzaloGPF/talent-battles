<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;

class ActivityController extends Controller
{
    /**
     * Display a listing of the Activity.
     */
    public function index(): JsonResponse
    {
        $activities = Activity::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(ActivityResource::collection($activities));
    }
}
