<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;

class SalesCardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return $this->dataResponse(tl('sales'), [
            'total' => Sale::count(),
            'amount' => Sale::query()->sum('total'),
        ]);
    }
}
