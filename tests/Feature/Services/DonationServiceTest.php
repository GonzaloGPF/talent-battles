<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\DonationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DonationServiceTest extends TestCase
{
    use RefreshDatabase;

    private DonationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(DonationService::class);
    }

    #[Test]
    public function it_can_create_a_donation()
    {
        $donation = $this->service->create(123, 'Some Message');

        $this->assertEquals(123, $donation->amount);
        $this->assertEquals('Some Message', $donation->message);
        $this->assertNull($donation->created_by);
    }

    #[Test]
    public function it_can_create_a_donation_of_a_given_user()
    {
        /** @var User $user */
        $user = create(User::class);
        $donation = $this->service->create(123, 'Some Message', $user->id);

        $this->assertEquals(123, $donation->amount);
        $this->assertEquals('Some Message', $donation->message);
        $this->assertEquals($user->id, $donation->created_by);
    }
}
