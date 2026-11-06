<?php

namespace Tests\Unit\Listeners;

use App\Events\InvitationCreated;
use App\Listeners\SendInvitationEmail;
use App\Models\Invitation;
use App\Models\User;
use App\Services\EmailService;
use Exception;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendInvitationEmailTest extends TestCase
{
    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creator = make(User::class, ['name' => 'someCreator']);
        $this->invitation = make(Invitation::class, [
            'email' => 'some@email.com',
            'name' => 'someUserName',
        ]);
        $this->invitation->setRelation('createdBy', $this->creator);
    }

    #[Test]
    public function it_listens_to_sales_created()
    {
        Event::fake();
        Event::assertListening(InvitationCreated::class, SendInvitationEmail::class);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_send_creation_sold_email_when_sales_created_is_dispatched()
    {
        $this->mock(EmailService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createInvitationEmail')
                ->once()
                ->with($this->invitation);
        });
        $event = new InvitationCreated($this->invitation);
        $listener = resolve(SendInvitationEmail::class);
        $listener->handle($event);
    }
}
