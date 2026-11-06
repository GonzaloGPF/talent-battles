<?php

namespace App\Providers;

use App\Events\BattleFinished;
use App\Events\BattleInProgress;
use App\Events\CommentCreated;
use App\Events\DownloadCreated;
use App\Events\FavoriteCreated;
use App\Events\InvitationCreated;
use App\Events\LikeCreated;
use App\Events\OrderSuccess;
use App\Events\ReportClosed;
use App\Events\ReportCreated;
use App\Events\SalesCreated;
use App\Events\UserRegistered;
use App\Listeners\AssociateInvitation;
use App\Listeners\CreateActivity;
use App\Listeners\CreateNotification;
use App\Listeners\CreateSales;
use App\Listeners\GiveBattlePoints;
use App\Listeners\HandleStripeWebhook;
use App\Listeners\PayToSeller;
use App\Listeners\ProcessCreationLines;
use App\Listeners\ProcessDonationLines;
use App\Listeners\ProcessPointLines;
use App\Listeners\ProcessPromoLines;
use App\Listeners\SendCreationSoldEmail;
use App\Listeners\SendInvitationEmail;
use App\Listeners\SendOrderSuccessEmail;
use App\Listeners\SendVerificationEmail;
use App\Listeners\SetBattleWinner;
use App\Listeners\SetPaymentAsSuccess;
use App\Listeners\SyncGuestCart;
use App\Listeners\UpdateUserRating;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Cashier\Events\WebhookReceived;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserRegistered::class => [
            SendVerificationEmail::class,
            AssociateInvitation::class,
        ],
        Login::class => [
            SyncGuestCart::class,
        ],
        OrderSuccess::class => [
            SetPaymentAsSuccess::class,
            ProcessCreationLines::class,
            ProcessPromoLines::class,
            ProcessPointLines::class,
            ProcessDonationLines::class,
            CreateActivity::class,
            CreateNotification::class,
            SendOrderSuccessEmail::class,
            CreateSales::class,
        ],
        SalesCreated::class => [
            PayToSeller::class,
            CreateNotification::class,
            SendCreationSoldEmail::class,
        ],
        BattleInProgress::class => [
            CreateNotification::class,
        ],
        BattleFinished::class => [
            SetBattleWinner::class,
            GiveBattlePoints::class,
            UpdateUserRating::class,
            CreateActivity::class,
            CreateNotification::class,
        ],
        LikeCreated::class => [
            CreateActivity::class,
            CreateNotification::class,
        ],
        FavoriteCreated::class => [
            CreateActivity::class,
            CreateNotification::class,
        ],
        CommentCreated::class => [
            CreateActivity::class,
            CreateNotification::class,
        ],
        DownloadCreated::class => [
            CreateActivity::class,
            CreateNotification::class,
        ],
        ReportCreated::class => [
            CreateNotification::class,
        ],
        InvitationCreated::class => [
            SendInvitationEmail::class,
        ],
        ReportClosed::class => [
            CreateNotification::class,
        ],
        WebhookReceived::class => [
            HandleStripeWebhook::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
