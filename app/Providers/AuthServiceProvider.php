<?php

namespace App\Providers;

use App\Enums\Roles;
use App\Interfaces\HasCreatedByInterface;
use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('is_owner', function (User $user, HasCreatedByInterface&Model $model) {
            return $user->id === $model->getAttribute('created_by');
        });

        /*
         * The activity of the whole site is for the staff: admins and managers.
         */
        Gate::define('view_platform_activity', fn (User $user) => hasRole(Roles::MANAGER, true, $user));
    }
}
