<?php

namespace App\Listeners;

use App\Events\SalesCreated;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Support\Collection;

class SendCreationSoldEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private readonly EmailService $emailService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SalesCreated $event): void
    {
        $event->sales
            ->groupBy('created_by')
            ->each(function (Collection $sales, int $userId) {
                /** @var User $user */
                $user = $sales->first()->createdBy;

                if (! $user) {
                    return;
                }

                $this->emailService->createCreationSold($sales, $user);
            });
    }
}
