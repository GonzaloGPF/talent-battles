<?php

namespace App\Listeners;

use App\Models\Cart;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class SyncGuestCart
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;
        $guestId = CartService::getGuestId();

        if (! $guestId) {
            return;
        }

        $cart = Cart::withoutGlobalScopes()
            ->whereNull('created_by')
            ->firstWhere('guest_id', $guestId);

        if (! $cart) {
            return;
        }

        $cart->forceFill(['created_by' => $user->id])
            ->save();
    }
}
