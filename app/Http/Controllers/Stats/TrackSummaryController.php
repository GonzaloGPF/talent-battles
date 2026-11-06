<?php

namespace App\Http\Controllers\Stats;

use App\Enums\TrackTypes;
use App\Helpers\ColorHelper;
use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class TrackSummaryController extends Controller
{
    public function __invoke(ColorHelper $colorHelper): JsonResponse
    {
        $tracks = Track::select(['id', 'created_by', 'track_type'])
            ->filter([
                'created_by' => auth()->id(),
            ])->get();

        $label = modelTitle('track', true);

        $grouped = $tracks
            ->groupBy('track_type.value')
            ->sortkeys();

        return $this->dataResponse($label, [
            'labels' => $grouped
                ->keys()
                ->map(fn (string $trackType) => tConstValue(TrackTypes::class, $trackType))
                ->toArray(),
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => $grouped
                        ->keys()
                        ->map(fn (string $trackType) => TrackTypes::fromValue($trackType)?->getColor())
                        ->values()
                        ->toArray(),
                    'data' => $grouped
                        ->map(fn (Collection $tracks) => $tracks->count())
                        ->values()
                        ->toArray(),
                ],
            ],
        ]);
    }
}
