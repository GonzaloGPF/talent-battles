<?php

namespace Tests\Feature\Filters;

use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->user = $this->signIn();
        create(Notification::class, [], 3);
    }

    #[Test]
    public function it_can_filter_notifications_by_noty_id()
    {
        create(Notification::class, ['data' => ['noty_id' => 'test']]);

        $notifications = Notification::filter(['noty' => 'test'])->get();

        $this->assertCount(1, $notifications);
    }

    #[Test]
    public function it_can_filter_notifications_by_message_keys()
    {
        create(Notification::class, ['data' => ['message_keys' => ['some', 'keys']]]);

        $notifications = Notification::filter(['message_keys' => 'keys'])->get();

        $this->assertCount(1, $notifications);
    }

    #[Test]
    public function it_can_filter_notifications_by_level()
    {
        create(Notification::class, ['data' => ['level' => 'test']]);

        $notifications = Notification::filter(['level' => 'test'])->get();

        $this->assertCount(1, $notifications);
    }

    #[Test]
    public function it_can_filter_notifications_by_type()
    {
        create(Notification::class, ['type' => 'Some Type']);

        $notifications = Notification::filter(['type' => 'Some Type'])->get();

        $this->assertCount(1, $notifications);
    }

    #[Test]
    public function it_can_filter_notifications_by_is_read()
    {
        Notification::query()->update(['read_at' => null]);

        create(Notification::class, ['read_at' => now()]);

        $notifications = Notification::filter(['is_read' => true])->get();

        $this->assertCount(1, $notifications);
    }
}
