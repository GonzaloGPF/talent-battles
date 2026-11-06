<?php

namespace Tests\Feature\Stats;

use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewCreationSalesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
    }

    #[Test]
    public function it_let_view_creation_sale_stats_data()
    {
        create(Sale::class, ['created_at' => today()->subMonths(13)]); // should be ignored
        $sale1 = create(Sale::class, ['created_at' => today()->subMonths(11)]);
        $sale2 = create(Sale::class, ['created_at' => today()->subMonths(3)]);
        $sale3 = create(Sale::class, ['created_at' => today()->subMonths(3)]);
        $sale4 = create(Sale::class, ['created_at' => today()]);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(13, $data['datasets'][0]['data']);
        $this->assertCount(13, $data['datasets'][1]['data']);

        //        $this->assertEquals([0, 1, 0, 0, 0, 0, 0, 0, 0, 2, 0, 1, 0,], $data['datasets'][0]['data']);
        //        $this->assertEquals([0, $sale1->total, 0, 0, 0, 0, 0, 0, 0, $sale2->total + $sale3->total, 0, $sale4->total, 0], $data['datasets'][1]['data']);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('stats.creation.sales'));
    }
}
