<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class TrackSummaryCardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $data = Track::select(['id', 'track_type', 'created_by'])
            ->filter([
                'created_by' => auth()->id(),
            ])->get()
            ->groupBy('track_type.value')
            ->mapWithKeys(fn (Collection $tracks) => [$tracks->first()->track_type->value => $tracks->count()])
            ->toArray();

        return $this->dataResponse(modelTitle('track', true), $data);
    }
}
