<?php

namespace App\Http\Controllers\Stats;

use App\Enums\ActivityTypes;
use App\Helpers\ColorHelper;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use BackedEnum;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ActivityReceivedController extends Controller
{
    public function __invoke(ColorHelper $colorHelper): JsonResponse
    {
        $startDate = today()->subYear()->addDay();
        $endDate = today()->addMonth();

        $activities = Activity::select(['id', 'receiver_id', 'created_by', 'created_at', 'activity_type'])
            ->filter([
                'min_created_at' => $startDate->toDateString(),
                'max_created_at' => $endDate->toDateString(),
                'not_created_by' => auth()->id(),
                'receiver_by' => auth()->id(),
            ])->get();

        $period = createPeriod($startDate, $endDate);

        return $this->dataResponse(trans('charts.creations'), [
            'labels' => $period->map(fn (Carbon $date) => $date->format('M'))->toArray(),
            'datasets' => [
                [
                    'label' => modelTitle('comment', true),
                    'backgroundColor' => ActivityTypes::DO_COMMENT->getColor(),
                    'borderColor' => ActivityTypes::DO_COMMENT->getColor(),
                    'data' => $period
                        ->map(fn (Carbon $date) => $this->countByMonthAndType($activities, $date, ActivityTypes::DO_COMMENT))
                        ->values()
                        ->toArray(),
                ],
                [
                    'label' => modelTitle('like', true),
                    'backgroundColor' => ActivityTypes::DO_LIKE->getColor(),
                    'borderColor' => ActivityTypes::DO_LIKE->getColor(),
                    'data' => $period
                        ->map(fn (Carbon $date) => $this->countByMonthAndType($activities, $date, ActivityTypes::DO_LIKE))
                        ->values()
                        ->toArray(),
                ],
                [
                    'label' => modelTitle('favorite', true),
                    'backgroundColor' => ActivityTypes::DO_FAVORITE->getColor(),
                    'borderColor' => ActivityTypes::DO_FAVORITE->getColor(),
                    'data' => $period
                        ->map(fn (Carbon $date) => $this->countByMonthAndType($activities, $date, ActivityTypes::DO_FAVORITE))
                        ->values()
                        ->toArray(),
                ],
                [
                    'label' => modelTitle('download', true),
                    'backgroundColor' => ActivityTypes::DO_DOWNLOAD->getColor(),
                    'borderColor' => ActivityTypes::DO_DOWNLOAD->getColor(),
                    'data' => $period
                        ->map(fn (Carbon $date) => $this->countByMonthAndType($activities, $date, ActivityTypes::DO_DOWNLOAD))
                        ->values()
                        ->toArray(),
                ],
            ],
        ]);
    }

    private function countByMonthAndType(Collection $activities, Carbon $date, BackedEnum $type): int
    {
        $activitiesByType = $activities
            ->filter(fn (Activity $activity) => $activity->activity_type === $type);

        return $this->groupByMonth($activitiesByType, $date)->count();
    }

    private function groupByMonth(Collection $activities, Carbon $date): Collection
    {
        return $activities
            ->filter(fn (Activity $activity) => $activity->created_at?->month === $date->month && $activity->created_at?->year && $date->year);
    }
}
