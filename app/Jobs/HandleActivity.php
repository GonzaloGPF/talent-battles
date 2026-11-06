<?php

namespace App\Jobs;

use App\Enums\ActivityTypes;
use App\Interfaces\HasCreatedByInterface;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleActivity implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public ActivityTypes $activityType, public HasCreatedByInterface $target, public ?User $receiver = null) {}

    /**
     * Execute the job.
     */
    public function handle(ActivityService $activityService): void
    {
        $activityService->create($this->activityType->value, $this->target, $this->receiver);
    }
}
