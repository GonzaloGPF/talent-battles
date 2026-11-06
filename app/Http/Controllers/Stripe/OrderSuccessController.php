<?php

namespace App\Http\Controllers\Stripe;

use App\Enums\PaymentStates;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Reporters\SessionMessage;
use App\Services\PaymentService;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;

class OrderSuccessController extends Controller
{
    public function __construct(private readonly UserService $userService, private readonly PaymentService $paymentService)
    {
        parent::__construct();
    }

    /**
     * @throws AuthorizationException
     */
    public function __invoke(int $orderId): View
    {
        /** @var Order $order */
        $order = Order::withoutGlobalScopes()->findOrFail($orderId);

        if ($order->payment_state !== PaymentStates::SUCCESS) {
            $this->authorize('update', $order); // TODO: test it

            if ($this->paymentService->confirmOrder($order)) {
                SessionMessage::flashMessage(tAction('confirmed', null, false));
            }
        }

        return view('app')->with(['user' => $this->userService->getUserData()]);
    }
}
