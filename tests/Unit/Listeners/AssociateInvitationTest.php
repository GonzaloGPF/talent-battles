<?php

namespace Tests\Unit\Listeners;

use App\Events\UserRegistered;
use App\Listeners\AssociateInvitation;
use App\Models\User;
use App\Services\InvitationService;
use Exception;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssociateInvitationTest extends TestCase
{
    #[Test]
    public function it_listens_to_user_registered()
    {
        Event::fake();
        Event::assertListening(UserRegistered::class, AssociateInvitation::class);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_syncs_to_a_given_user()
    {
        /** @var User $user */
        $user = make(User::class);

        $this->mock(InvitationService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('associateToUser')
                ->once()
                ->with($user);
        });

        $event = new UserRegistered($user);
        $listener = resolve(AssociateInvitation::class);
        $listener->handle($event);
    }
}
