<?php

namespace Tests\Feature\Donations;

use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewDonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Donation::class, [], 3);
    }

    #[Test]
    public function it_let_fetch_donations()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(3, $data);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('donations.index'));
    }
}
