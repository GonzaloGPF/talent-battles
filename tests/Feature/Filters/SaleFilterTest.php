<?php

namespace Tests\Feature\Filters;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaleFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        create(Sale::class, [], 3);
    }

    #[Test]
    public function it_let_filter_sales_by_orderable_name()
    {
        /** @var Product $product */
        $product = create(Product::class, ['name' => 'Test']);
        create(Sale::class, [
            'product_id' => $product->id,
        ]);

        $data = $this->makeRequest(['name' => 'Test']);

        $this->assertCount(1, $data);
    }

    private function makeRequest(array $attributes): array
    {
        return $this->getJson(route('sales.index', $attributes))
            ->assertSuccessful()
            ->json('data');
    }
}
