<?php

namespace Tests\Feature\Services;

use App\Events\InvitationCreated;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationService;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvitationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->service = resolve(InvitationService::class);
    }

    #[Test]
    public function it_can_create_an_invitation()
    {
        Event::fake();

        $invitation = $this->service->create([
            'name' => 'someName',
            'email' => 'some@email.com',
        ]);

        $this->assertEquals('someName', $invitation->name);
        $this->assertEquals('some@email.com', $invitation->email);
        $this->assertEquals(auth()->id(), $invitation->created_by);
        $this->assertNull($invitation->user_id);

        Event::assertDispatched(InvitationCreated::class);
    }

    #[Test]
    public function it_can_create_multiple_invitations()
    {
        Event::fake();

        $invitations = $this->service->createMultiple([
            [
                'name' => 'someName',
                'email' => 'some@email.com',
            ],
            [
                'name' => 'someOtherName',
                'email' => 'some_other@email.com',
            ],
        ]);

        $this->assertCount(2, $invitations);

        Event::assertDispatchedTimes(InvitationCreated::class, 2);
    }

    #[Test]
    public function when_creating_an_invitation_it_will_ignore_already_created_email()
    {
        Event::fake();

        create(Invitation::class, ['email' => 'some@email.com']);

        $invitation = $this->service->create([
            'name' => 'someName',
            'email' => 'some@email.com',
        ]);

        $this->assertNull($invitation);

        Event::assertNotDispatched(InvitationCreated::class);
    }

    #[Test]
    public function it_let_associate_invitation_to_its_user_by_email()
    {
        /** @var User $user */
        $user = create(User::class, ['verified_at' => null]);
        /** @var Invitation $invitation */
        $invitation = create(Invitation::class, [
            'created_by' => create(User::class)->id,
            'email' => $user->email,
            'user_id' => null,
        ]);

        $this->service->associateToUser($user);

        $this->assertEquals($user->id, $invitation->fresh()->user_id);
        $this->assertNotNull($user->fresh()->verified_at);
    }
}
