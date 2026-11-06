<?php

namespace Tests\Feature\Filters;

use App\Enums\ProductTypes;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Product::class, [], 3);
    }

    #[Test]
    public function it_can_filter_products_by_name()
    {
        create(Product::class, ['name' => 'someName']);

        $data = $this->makeRequest(['name' => 'someName'])
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_products_by_description()
    {
        create(Product::class, ['description' => 'someDescription']);

        $data = $this->makeRequest(['description' => 'someDescription'])
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_products_by_exclusive()
    {
        Product::query()->update(['exclusive' => false]);

        create(Product::class, ['exclusive' => true]);

        $data = $this->makeRequest(['exclusive' => true])
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_products_by_promoted()
    {
        Product::query()->update(['promoted' => null]);

        create(Product::class, ['promoted' => today()]);

        create(Product::class, ['promoted' => today()->addMonth()]);

        create(Product::class, ['promoted' => today()->subMonth()]);

        $products = Product::filter(['promoted' => true])->get();

        $this->assertCount(2, $products);
    }

    #[Test]
    public function it_can_filter_products_by_product_type()
    {
        Product::query()->update(['product_type' => ProductTypes::PROMO]);

        create(Product::class, ['product_type' => ProductTypes::POINTS]);

        $data = Product::filter(['product_types' => [ProductTypes::POINTS->value]])->get();

        $this->assertCount(1, $data);
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->getJson(route('products.index', $attributes));
    }
}
