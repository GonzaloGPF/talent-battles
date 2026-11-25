<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use BackedEnum;
use Exception;
use Illuminate\Database\Eloquent\Model;

class ActivityService
{
    public function create(string|BackedEnum $activityType, Model $target, ?User $receiver = null): ?Activity
    {
        if ($target instanceof User) {
            $receiver = $target;
        }

        try {
            $activity = new Activity;
            $activity->activity_type = $activityType;
            $activity->receiver_id = $receiver?->id ?? $target->getAttribute('created_by');

            $activity->target()->associate($target);

            $activity->save();

            return $activity;
        } catch (Exception $exception) {
            //            Logger::exception($exception);
            return null;
        }
    }
}
