<?php

namespace App\Http\Controllers\Stats;

use App\Enums\ActivityTypes;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;

class ActivitySummaryCardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $userId = auth()->id();

        return $this->dataResponse(tl('profile'), [
            'total' => $this->getCountByType([
                ActivityTypes::DO_COMMENT,
                ActivityTypes::DO_LIKE,
                ActivityTypes::DO_FAVORITE,
                ActivityTypes::DO_DOWNLOAD,
            ]),
            'comments' => $this->getCountByType(ActivityTypes::DO_COMMENT),
            'favorites' => $this->getCountByType(ActivityTypes::DO_FAVORITE),
            'likes' => $this->getCountByType(ActivityTypes::DO_LIKE),
            'downloads' => $this->getCountByType(ActivityTypes::DO_DOWNLOAD),
        ]);
    }

    private function getCountByType(ActivityTypes|array $activityType): int
    {
        $types = is_array($activityType) ? $activityType : [$activityType];

        return Activity::filter([
            'receiver' => [auth()->id()],
            'activity_types' => $types,
        ])->count();
    }
}
