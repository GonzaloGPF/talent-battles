<?php

namespace App\Http\Controllers;

use App\Http\Resources\RegionResource;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    /**
     * Display a listing of the Region.
     */
    public function index(): JsonResponse
    {
        $regions = Region::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(RegionResource::collection($regions));
    }
}
