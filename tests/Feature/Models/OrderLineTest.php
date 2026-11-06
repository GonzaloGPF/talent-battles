<?php

namespace Tests\Feature\Models;

use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderLineTest extends TestCase
{
    use RefreshDatabase;

    private OrderLine $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(OrderLine::class);
    }

    #[Test]
    public function order_line_belongs_to_a_seller()
    {
        $this->assertBelongsTo($this->model, User::class, 'seller');
    }

    #[Test]
    public function order_line_belongs_to_an_order()
    {
        $this->assertBelongsTo($this->model, Order::class);
    }

    #[Test]
    public function order_line_belongs_to_a_product()
    {
        $this->assertBelongsTo($this->model, Product::class);
    }
}
