<?php

namespace App\Services;

use App\Enums\PaymentStates;
use App\Helpers\Calculator;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\User;
use Illuminate\Support\Collection;

class OrderService
{
    public function __construct(private readonly Calculator $calculator) {}

    public function createFromCart(Cart $cart, string $email, ?array $meta = null): ?Order
    {
        $orderLines = resolve(OrderLineService::class)
            ->makeFromCart($cart);

        return $this->create($orderLines, $email, $meta);
    }

    private function create(Collection $orderLines, ?string $email = null, ?array $meta = null): ?Order
    {
        if ($orderLines->isEmpty()) {
            return null;
        }

        /** @var User|null $buyer */
        $buyer = User::firstWhere('email', $email);

        $brute = $orderLines->sum('subtotal');
        $discountAmount = $orderLines->sum('discount_amount');
        $taxAmount = $orderLines->sum('tax_amount');
        $totals = $this->calculator->getOrderTotals($brute, $discountAmount, $taxAmount);

        $order = (new Order)
            ->forceFill(array_merge([
                'created_by' => $buyer?->id,
                'guest_id' => CartService::getOrCreateGuestId(),
                'email' => $buyer?->email ?? $email,
                'payment_state' => PaymentStates::PENDING,
                'address' => $buyer?->address,
                'meta' => $meta,
            ], $totals));

        $order->save();

        $orderLines->each(fn (OrderLine $orderLine) => $orderLine->forceFill(['created_by' => $buyer?->id]));

        $order->orderLines()->saveMany($orderLines);

        return $order;
    }
}
