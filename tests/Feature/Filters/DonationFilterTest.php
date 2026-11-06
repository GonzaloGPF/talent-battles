<?php

namespace Tests\Feature\Filters;

use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DonationFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Donation::class, [], 3);
    }

    #[Test]
    public function it_filters_donations_by_message()
    {
        create(Donation::class, ['message' => 'some message']);

        $data = Donation::filter(['message' => 'some message'])->get();

        $this->assertCount(1, $data);
    }
}
