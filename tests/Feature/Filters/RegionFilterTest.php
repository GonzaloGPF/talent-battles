<?php

namespace Tests\Feature\Filters;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegionFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Region::class);
    }

    #[Test]
    public function it_can_filter_regions_by_country()
    {
        Region::query()->update([
            'country_id' => Country::first()->id,
        ]);

        /** @var Country $country */
        $country = create(Country::class, ['code' => 'test']);
        create(Region::class, [
            'country_id' => $country->id,
            'code' => 'new',
        ]);

        $data = Region::filter(['country' => [$country->id]])->get();

        $this->assertCount(1, $data);
    }
}
