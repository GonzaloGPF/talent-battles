<?php

namespace App\Services;

use App\Interfaces\HasCreatedByInterface;
use App\Models\Activity;
use App\Models\User;
use BackedEnum;
use Exception;

class ActivityService
{
    public function create(string|BackedEnum $activityType, HasCreatedByInterface|User $target, ?User $receiver = null): ?Activity
    {
        if ($target instanceof User) {
            $receiver = $target;
        }

        try {
            $activity = new Activity;
            $activity->activity_type = $activityType;
            $activity->receiver_id = $receiver?->id ?? $target->created_by;

            $activity->target()->associate($target);

            $activity->save();

            return $activity;
        } catch (Exception $exception) {
            //            Logger::exception($exception);
            return null;
        }
    }
}
