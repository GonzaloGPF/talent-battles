<?php

namespace Tests\Feature\Invitations;

use App\Events\InvitationCreated;
use App\Models\Invitation;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateInvitationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->user = $this->signIn();
    }

    #[Test]
    public function it_let_users_create_invitations_by_giving_email_addresses()
    {
        $this->makeRequest(['invitations' => [
            [
                'name' => 'someUser',
                'email' => 'some@email.com',
            ],
            [
                'name' => null,
                'email' => 'other@email.com',
            ],
        ]])->assertSuccessful();

        $invitations = Invitation::all();

        /** @var Invitation $invitation */
        $invitation = $invitations->first();
        $this->assertEquals('some@email.com', $invitation->email);
        $this->assertEquals('someUser', $invitation->name);
        $this->assertEquals($this->user->id, $invitation->created_by);

        /** @var Invitation $invitation */
        $invitation = $invitations->last();
        $this->assertEquals('other@email.com', $invitation->email);
        $this->assertNull($invitation->name);
        $this->assertEquals($this->user->id, $invitation->created_by);

        Event::assertDispatchedTimes(InvitationCreated::class, 2);
    }

    #[Test]
    public function it_wont_let_create_invitation_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('invitations.store'), $attributes);
    }
}
