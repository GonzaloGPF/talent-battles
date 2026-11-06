<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the Sale.
     */
    public function index(): JsonResponse
    {
        $sales = Sale::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(SaleResource::collection($sales));
    }
}
