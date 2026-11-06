<?php

namespace App\Http\Controllers\Stats;

use App\Helpers\ColorHelper;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class CreationSalesController extends Controller
{
    public function __invoke(ColorHelper $colorHelper): JsonResponse
    {
        $startDate = today()->subYear()->addDay();
        $endDate = today()->addMonth();

        $sales = Sale::filter([
            'min_created_at' => $startDate->toDateString(),
            'max_created_at' => $endDate->toDateString(),
        ])->get();

        $period = createPeriod($startDate, $endDate);

        return $this->dataResponse(trans('charts.sales'), [
            'labels' => $period
                ->map(fn (Carbon $date) => $date->format('M'))
                ->toArray(),

            'datasets' => [
                [
                    'label' => modelTitle('sale', true),
                    'backgroundColor' => '#2980b9',
                    'borderColor' => '#2980b9',
                    'yAxisID' => 'y1',
                    'type' => 'line',
                    'data' => $period
                        ->map(fn (Carbon $date) => $this->groupByMonth($sales, $date)->count())
                        ->values()
                        ->toArray(),
                ],
                [
                    'label' => ta('total'),
                    'backgroundColor' => '#27ae60',
                    'yAxisID' => 'y',
                    'data' => $period
                        ->map(fn (Carbon $date) => $this->groupByMonth($sales, $date)->sum('total'))
                        ->values()
                        ->toArray(),
                ],
            ],
        ]);
    }

    private function groupByMonth(Collection $sales, Carbon $date): Collection
    {
        return $sales
            ->filter(fn (Sale $sale) => $sale->created_at?->month === $date->month && $sale->created_at?->year === $date->year);
    }
}
