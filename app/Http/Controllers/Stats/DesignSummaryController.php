<?php

namespace App\Http\Controllers\Stats;

use App\Enums\DesignTypes;
use App\Helpers\ColorHelper;
use App\Http\Controllers\Controller;
use App\Models\Design;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class DesignSummaryController extends Controller
{
    public function __invoke(ColorHelper $colorHelper): JsonResponse
    {
        $designs = Design::select(['id', 'design_type', 'created_by'])
            ->filter([
                'created_by' => auth()->id(),
            ])->get();

        $label = modelTitle('design', true);

        $grouped = $designs
            ->groupBy('design_type.value')
            ->sortkeys();

        return $this->dataResponse($label, [
            'labels' => $grouped
                ->keys()
                ->map(fn (string $designType) => tConstValue(DesignTypes::class, $designType))
                ->toArray(),
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => $grouped
                        ->keys()
                        ->map(fn (string $designType) => DesignTypes::fromValue($designType)?->getColor())
                        ->values()
                        ->toArray(),
                    'data' => $grouped
                        ->map(fn (Collection $designs) => $designs->count())
                        ->values()
                        ->toArray(),
                ],
            ],
        ]);
    }
}
