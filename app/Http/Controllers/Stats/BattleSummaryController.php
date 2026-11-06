<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Models\Battle;
use Illuminate\Http\JsonResponse;

class BattleSummaryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $userId = auth()->id();

        return $this->dataResponse(trans('charts.battles'), [
            'labels' => [tl('won'), tl('lost')],
            'datasets' => [
                [
                    'label' => modelTitle('battle', true),
                    'backgroundColor' => [
                        '#27ae60',
                        '#e74c3c',
                    ],
                    'data' => [
                        Battle::filter([
                            'by_participants' => [$userId],
                            'won' => true,
                        ])->count(),
                        Battle::filter([
                            'by_participants' => [$userId],
                            'won' => false,
                        ])->count(),
                    ],
                ],
            ],
        ]);
    }
}
