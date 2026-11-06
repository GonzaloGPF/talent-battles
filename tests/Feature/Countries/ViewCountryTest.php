<?php

namespace Tests\Feature\Countries;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewCountryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Country::class, ['code' => 'es']);
        create(Country::class, ['code' => 'gb']);
        create(Country::class, ['code' => 'us']);
    }

    #[Test]
    public function it_let_view_all_countries()
    {
        $data = $this->getJson(route('countries.index'))
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(3, $data);
    }
}
