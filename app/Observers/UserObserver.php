<?php

namespace App\Observers;

use App\Enums\Levels;
use App\Models\User;
use App\Services\UserService;

class UserObserver
{
    public function __construct(private readonly UserService $userService) {}

    public function updating(User $user): void
    {
        if ($user->isDirty('email')) {
            $user->verified_at = null;
        }
    }

    public function updated(User $user): void
    {
        if ($user->hasStripeId()) {
            $this->userService->syncStripeDetails($user);
        }
    }

    public function creating(User $user): void
    {
        $user->points = config('defaults.initial_points');
        $user->social_settings = array_merge(config('defaults.social_settings'), $user->social_settings ?? []);
        $user->notification_settings = array_merge(config('defaults.notification_settings'), $user->notification_settings ?? []);
    }

    public function saving(User $user): void
    {
        $user->level = Levels::from($this->userService->getLevel($user->experience ?? 0));
    }

    public function saved(User $user): void
    {
        //        $this->stripeApi->createOrUpdateStripeCustomer($user);
    }
}
