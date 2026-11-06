<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class OrderSeeder extends Seeder
{
    private Collection $users;

    private OrderService $orderService;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->users = User::select(['id', 'email'])->get();

        $this->orderService = resolve(OrderService::class);

        Cart::all()
            ->each(function (Cart $cart) {
                if (rand(1, 10) > 7) {
                    return;
                }

                $this->orderService->createFromCart($cart, $this->users->random()->email);
            });
    }
}
