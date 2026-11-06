<?php

namespace Tests\Unit\Helpers;

use App\Enums\TrackTypes;
use App\Helpers\NotificationHelper;
use App\Models\Album;
use App\Models\Like;
use App\Models\Track;
use App\Models\User;
use App\Notifications\LikeReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationHelperTest extends TestCase
{
    use RefreshDatabase;

    private NotificationHelper $notificationHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationHelper = resolve(NotificationHelper::class);
    }

    #[Test]
    public function it_knows_what_via_should_use_a_notification_depending_on_user_settings()
    {
        $user1 = make(User::class, [
            'notification_settings' => [
                'like_received' => [
                    'mail' => true,
                    'broadcast' => true,
                ],
            ],
        ]);

        $user2 = make(User::class, [
            'notification_settings' => [
                'like_received' => [
                    'mail' => false,
                    'broadcast' => false,
                ],
            ],
        ]);

        $user1Via = $this->notificationHelper->getVia(LikeReceived::class, $user1);
        $this->assertEquals(['database', 'mail', 'broadcast'], $user1Via);

        $user2Via = $this->notificationHelper->getVia(LikeReceived::class, $user2);
        $this->assertEquals(['database'], $user2Via);
    }

    #[Test]
    public function it_knows_how_to_transforms_a_notification_to_array()
    {
        /** @var Album $album */
        $album = make(Album::class, ['id' => 123]);

        /** @var Like $like */
        $like = make(Like::class);
        $like->setRelations([
            'createdBy' => make(User::class),
            'likeable' => $album,
        ]);

        $notification = new LikeReceived($like);

        $arrayData = $this->notificationHelper->toArray($notification);

        $this->assertTrue(str_starts_with($arrayData['noty_id'], 'noty_'));
        $this->assertEquals(NotificationHelper::INFO, $arrayData['level']);
        $this->assertEquals(url('albums/123'), $arrayData['url']);
    }

    #[Test]
    public function when_transforming_a_notification_to_array_it_generates_correct_url_if_target_is_a_track()
    {
        /** @var Track $track */
        $track = make(Track::class, [
            'id' => 321,
            'track_type' => TrackTypes::BEAT,
        ]);

        /** @var Like $like */
        $like = make(Like::class);
        $like->setRelations([
            'createdBy' => make(User::class),
            'likeable' => $track,
        ]);

        $notification = new LikeReceived($like);

        $arrayData = $this->notificationHelper->toArray($notification);

        $this->assertEquals(url('beats/321'), $arrayData['url']);
    }
}
