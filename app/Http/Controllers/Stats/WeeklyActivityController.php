<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\PlatformActivityService;
use Illuminate\Http\JsonResponse;

class WeeklyActivityController extends Controller
{
    /**
     * What real users did in each of the last weeks, for the staff.
     */
    public function __invoke(PlatformActivityService $activityService): JsonResponse
    {
        $this->authorize('view_platform_activity');

        return $this->dataResponse(tl('weekly_activity'), [
            'weeks' => $activityService->weekly(),
        ]);
    }
}
