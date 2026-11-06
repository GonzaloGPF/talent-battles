<?php

namespace Tests\Feature\Models;

use App\Enums\ProductTypes;
use App\Models\Cart;
use App\Models\Product;
use App\Traits\HasCreatedBy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private Cart $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = create(Cart::class);
    }

    #[Test]
    public function cart_uses_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }

    #[Test]
    public function cart_can_calculates_its_total_correctly()
    {
        /** @var Product $product1 */
        $product1 = create(Product::class, [
            'product_type' => ProductTypes::TRACK,
            'price' => 100,
        ]);

        /** @var Product $product2 */
        $product2 = create(Product::class, [
            'product_type' => ProductTypes::DESIGN,
            'price' => 200,
        ]);

        /** @var Product $product3 */
        $product3 = create(Product::class, [
            'product_type' => ProductTypes::DONATION,
            'price' => 1,
        ]);

        $this->model->update([
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 1,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 1,
                ],
                [
                    'product_id' => $product3->id,
                    'quantity' => 50,
                ],
            ],
        ]);

        $this->assertEquals(350, $this->model->getTotal());
    }
}
