<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Models\Design;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class DesignSummaryCardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $data = Design::select(['id', 'design_type', 'created_by'])
            ->filter([
                'created_by' => auth()->id(),
            ])->get()
            ->groupBy('design_type.value')
            ->mapWithKeys(fn (Collection $designs) => [($designs->first()?->design_type?->value ?? 'none') => $designs->count()])
            ->toArray();

        return $this->dataResponse(modelTitle('design', true), $data);
    }
}
