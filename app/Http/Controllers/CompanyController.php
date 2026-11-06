<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companyService)
    {
        parent::__construct();
    }

    public function index(): JsonResponse
    {
        return $this->dataResponse(config('app.name'), $this->companyService->getData());
    }
}
