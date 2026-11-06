<?php

namespace Tests\Feature\Regions;

use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewRegionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Region::class, [], 3);
    }

    #[Test]
    public function it_let_view_all_regions()
    {
        $data = $this->getJson(route('regions.index'))
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(3, $data);
    }
}
