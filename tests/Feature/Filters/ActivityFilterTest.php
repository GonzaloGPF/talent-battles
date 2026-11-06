<?php

namespace Tests\Feature\Filters;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        create(Activity::class, [
            'target_type' => 'SomeType',
            'target_id' => 0,
        ]);
    }

    #[Test]
    public function it_can_filter_activities_by_receiver()
    {
        /** @var User $user */
        $user = create(User::class);
        create(Activity::class, [
            'created_by' => $user->id,
            'receiver_id' => $user->id,
            'target_type' => 'SomeType',
            'target_id' => 0,
        ]);

        $data = Activity::filter(['receiver' => [$user->id]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_activities_by_activity_types()
    {
        Activity::query()->update(['activity_type' => ActivityTypes::NEW_TRACK]);

        create(Activity::class, [
            'target_type' => 'SomeType',
            'target_id' => 0,
            'activity_type' => ActivityTypes::DO_COMMENT,
        ]);

        $data = Activity::filter(['activity_types' => [ActivityTypes::DO_COMMENT->value]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_activity_by_target_model()
    {
        Activity::query()->update(['target_type' => User::class]);

        create(Activity::class, [
            'target_type' => Track::class,
            'target_id' => 0,
        ]);

        $data = Activity::filter(['target_type' => ['track']])->get();

        $this->assertCount(1, $data);
    }
}
