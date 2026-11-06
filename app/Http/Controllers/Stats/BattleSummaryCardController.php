<?php

namespace App\Http\Controllers\Stats;

use App\Enums\BattleStates;
use App\Http\Controllers\Controller;
use App\Models\Battle;
use Illuminate\Http\JsonResponse;

class BattleSummaryCardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $userId = auth()->id();

        return $this->dataResponse(tl('battles'), [
            'total' => Battle::filter([
                'by_participants' => [$userId],
            ])->count(),
            'open' => Battle::filter([
                'by_participants' => [$userId],
            ])->where('battle_state', BattleStates::OPEN->value)
                ->count(),
            'in_progress' => Battle::filter([
                'by_participants' => [$userId],
            ])->where('battle_state', BattleStates::IN_PROGRESS->value)
                ->count(),
            'won' => Battle::filter([
                'by_participants' => [$userId],
                'won' => true,
            ])->count(),
            'lost' => Battle::filter([
                'by_participants' => [$userId],
                'won' => false,
            ])->count(),
        ]);
    }
}
