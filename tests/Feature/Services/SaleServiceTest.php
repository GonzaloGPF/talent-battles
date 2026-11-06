<?php

namespace Tests\Feature\Services;

use App\Enums\ProductTypes;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $service;

    private float $creationCommission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->service = resolve(SaleService::class);
        $this->creationCommission = config('company.fees.creation');
    }

    #[Test]
    public function it_can_create_a_sale_from_an_order_line()
    {
        $seller = create(User::class);

        /** @var Product $product */
        $product = create(Product::class, ['name' => 'Some Name']);

        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, [
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_type' => ProductTypes::TRACK->value,
            'price' => 100,
            'quantity' => 1,
            'discount' => 0,
            'tax' => 0,
        ]);

        $commissionAmount = $orderLine->total_with_taxes * ($this->creationCommission / 100);

        $sale = $this->service->createFrom($orderLine);

        $this->assertEquals($seller->id, $sale->created_by);
        $this->assertEquals(trans('help.you_sold_product', ['product' => 'Some Name']), $sale->description);
        $this->assertEquals(100, $sale->amount);
        $this->assertEquals($this->creationCommission, $sale->commission);
        $this->assertEquals($commissionAmount, $sale->commission_amount);
        $this->assertEquals(100 - $commissionAmount, $sale->total);
        $this->assertEquals('Some Name', $sale->product->name);
    }

    #[Test]
    public function it_wont_create_a_sale_if_its_not_an_order_line_creation()
    {
        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, [
            'product_type' => ProductTypes::POINTS->value,
        ]);

        $this->assertNull($this->service->createFrom($orderLine));
    }
}
