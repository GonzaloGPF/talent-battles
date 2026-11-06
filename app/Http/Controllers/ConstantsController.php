<?php

namespace App\Http\Controllers;

use App\Services\ConstantsService;
use Exception;
use Illuminate\Http\JsonResponse;

class ConstantsController extends Controller
{
    protected string $modelName = 'constants';

    protected bool $female = true;

    /**
     * Display a listing of all Constants.
     *
     * @throws Exception
     */
    public function index(ConstantsService $service): JsonResponse
    {
        return $this->dataResponse('index', $service->getConstants());
    }
}
