<?php

namespace App\Jobs\Stripe;

use App\Api\StripeApi;
use App\Facades\Logger;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleAccountUpdated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $payload
     * @return void
     */
    public function __construct(public array $payload) {}

    /**
     * The webhook only reports which account has changed, and it could be forged. The account saved is the one Stripe
     * returns, since it decides if the user can sell, and the user keeps theirs if Stripe cannot be reached.
     */
    public function handle(StripeApi $stripeApi): void
    {
        try {
            $accountId = data_get($this->payload, 'data.object.id');

            if (! is_string($accountId) || $accountId === '') {
                return;
            }

            $user = User::firstWhere('account->id', $accountId);

            if (! $user) {
                return;
            }

            $account = $stripeApi->getAccount($user);

            if (! $account) {
                return;
            }

            $user
                ->forceFill(['account' => $account->toArray()])
                ->save();
        } catch (Exception $e) {
            Logger::exception($e);
        }
    }
}
