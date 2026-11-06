<?php

namespace App\Listeners;

use App\Enums\ActivityTypes;
use App\Events\BattleFinished;
use App\Events\CommentCreated;
use App\Events\DownloadCreated;
use App\Events\FavoriteCreated;
use App\Events\LikeCreated;
use App\Events\OrderSuccess;
use App\Services\ActivityService;

class CreateActivity
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(public ActivityService $activityService)
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     */
    public function handle($event): void
    {
        if ($event instanceof LikeCreated) {
            $this->activityService->create(ActivityTypes::DO_LIKE, $event->like->likeable);
        }

        if ($event instanceof FavoriteCreated) {
            $this->activityService->create(ActivityTypes::DO_FAVORITE, $event->favorite->favoritable);
        }

        if ($event instanceof CommentCreated) {
            $this->activityService->create(ActivityTypes::DO_COMMENT, $event->comment->commentable);
        }

        if ($event instanceof DownloadCreated) {
            $this->activityService->create(ActivityTypes::DO_DOWNLOAD, $event->download->downloable);
        }

        if ($event instanceof OrderSuccess) {
            $this->activityService->create(ActivityTypes::DO_BUY, $event->order);
        }

        if ($event instanceof BattleFinished) {
            $winnerUser = $event->battle->winner?->createdBy;

            if (! $winnerUser) {
                return;
            }

            $this->activityService->create(ActivityTypes::WIN_BATTLE, $event->battle, $winnerUser);
        }
    }
}
