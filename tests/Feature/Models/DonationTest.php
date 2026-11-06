<?php

namespace Tests\Feature\Models;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    private Donation $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class, [
            'last_donation' => null,
        ]);
        $this->model = create(Donation::class, [
            'created_by' => $this->user->id,
        ]);
    }

    #[Test]
    public function when_creating_a_donation_it_creates_an_activity_too_if_it_has_a_creator()
    {
        /** @var Activity $activity */
        $activity = Activity::first();

        $this->assertEquals(ActivityTypes::DO_DONATION, $activity->activity_type);
        //        $this->assertEquals($this->user->id, $activity->receiver_id);
    }

    #[Test]
    public function when_creating_a_donation_it_wont_create_an_activity_if_it_has_not_a_creator()
    {
        Activity::query()->delete();

        create(Donation::class, [
            'created_by' => null,
        ]);

        $this->assertNull(Activity::first());
    }

    #[Test]
    public function when_creating_a_donation_it_updates_last_donation_of_creator()
    {
        $this->assertNotNull($this->user->fresh()->last_donation);
    }

    #[Test]
    public function donation_belongs_to_a_created_by()
    {
        $this->assertBelongsTo($this->model, User::class, 'createdBy');
    }
}
