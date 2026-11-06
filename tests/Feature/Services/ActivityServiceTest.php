<?php

namespace Tests\Feature\Services;

use App\Enums\ActivityTypes;
use App\Models\Track;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    private ActivityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(ActivityService::class);
    }

    #[Test]
    public function it_can_create_an_activity_for_a_given_model()
    {
        /** @var User $owner */
        $owner = create(User::class);
        /** @var Track $target */
        $target = create(Track::class, ['created_by' => $owner->id]);

        $activity = $this->service->create(ActivityTypes::DO_LIKE, $target);

        $this->assertEquals(ActivityTypes::DO_LIKE, $activity->activity_type);
        $this->assertEquals($owner->id, $activity->receiver_id);
        $this->assertEquals(Track::class, $activity->target_type);
        $this->assertEquals($target->id, $activity->target_id);
    }

    #[Test]
    public function when_creating_an_activity_it_will_set_the_target_the_same_as_the_model_if_model_if_an_user()
    {
        /** @var User $target */
        $target = create(User::class);

        $activity = $this->service->create(ActivityTypes::DO_LIKE, $target);

        $this->assertEquals(ActivityTypes::DO_LIKE, $activity->activity_type);
        $this->assertEquals($target->id, $activity->receiver_id);
        $this->assertEquals(User::class, $activity->target_type);
        $this->assertEquals($target->id, $activity->target_id);
    }
}
