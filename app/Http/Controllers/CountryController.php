<?php

namespace App\Http\Controllers;

use App\Http\Resources\CountryResource;
use App\Models\Country;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    /**
     * Display a listing of the Country.
     */
    public function index(): JsonResponse
    {
        $countries = Country::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(CountryResource::collection($countries));
    }
}
