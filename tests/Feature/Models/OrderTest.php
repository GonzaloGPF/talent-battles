<?php

namespace Tests\Feature\Models;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private Order $model;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->model = make(Order::class);
    }

    #[Test]
    public function order_has_many_order_lines()
    {
        $this->assertHasMany($this->model, OrderLine::class);
    }

    #[Test]
    public function it_cleans_cart_items_from_logged_user_when_created()
    {
        create(Cart::class, [
            'created_by' => $this->user->id,
            'items' => [
                [
                    'seller_id' => 'someId',
                    'orderable_id' => 'someId',
                    'orderable_type' => 'someType',
                    'description' => 'Some Description',
                    'type' => 'someType',
                    'price' => 123,
                    'quantity' => 1,
                ],
            ],
        ]);

        create(Order::class, ['created_by' => $this->user->id]);

        $this->assertCount(0, Cart::first()->items);
    }
}
