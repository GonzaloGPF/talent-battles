<?php

namespace App\Jobs\Stripe;

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
     * @return void
     */
    public function __construct(public array $payload) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $account = data_get($this->payload, 'data.object');

            if (empty($account['id'])) {
                return;
            }

            $user = User::firstWhere('account->id', $account['id']);

            if (! $user) {
                return;
            }

            $user
                ->forceFill(['account' => $account])
                ->save();
        } catch (Exception $e) {
            Logger::exception($e);
        }
    }
}
