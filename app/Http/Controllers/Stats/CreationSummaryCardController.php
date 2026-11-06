<?php

namespace App\Http\Controllers\Stats;

use App\Enums\TrackTypes;
use App\Http\Controllers\Controller;
use App\Models\Design;
use App\Models\Track;
use Illuminate\Http\JsonResponse;

class CreationSummaryCardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $totalTracks = Track::filter([
            'created_by' => auth()->id(),
        ])->count();

        $totalDesigns = Design::filter([
            'created_by' => auth()->id(),
        ])->count();

        return $this->dataResponse(tl('creations'), [
            'beats' => Track::filter([
                'created_by' => auth()->id(),
                'track_types' => [TrackTypes::BEAT->value],
            ])->count(),
            'samples' => Track::filter([
                'created_by' => auth()->id(),
                'track_types' => [TrackTypes::SAMPLE->value],
            ])->count(),
            'songs' => Track::filter([
                'created_by' => auth()->id(),
                'track_types' => [TrackTypes::SONG->value],
            ])->count(),
            'designs' => $totalDesigns,
            'total' => $totalTracks + $totalDesigns,
        ]);
    }
}
