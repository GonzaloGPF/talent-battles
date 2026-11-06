<?php

namespace Tests\Feature\Notifications;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReadNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        create(Notification::class);
    }

    #[Test]
    public function it_let_read_all_notifications()
    {
        $this->assertCount(1, $this->user->unreadNotifications);

        $this->putJson(route('notifications.read'))
            ->assertSuccessful();

        $this->assertCount(0, $this->user->fresh()->unreadNotifications);
    }

    #[Test]
    public function it_let_read_single_notification()
    {
        /** @var DatabaseNotification $notification */
        $notification = $this->user->notifications->first();

        $this->putJson(route('notifications.read', $notification))
            ->assertSuccessful();

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
