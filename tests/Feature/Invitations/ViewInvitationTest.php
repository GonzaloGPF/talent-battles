<?php

namespace Tests\Feature\Invitations;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        create(Invitation::class, [], 3);
    }

    #[Test]
    public function it_let_view_invitations()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(3, $data);
    }

    #[Test]
    public function it_let_view_invitations_with_associated_user_account_state()
    {
        /** @var User $user */
        $user = create(User::class, ['account' => 'someData']);

        $invitation = Invitation::first();
        $invitation->user()
            ->associate($user)
            ->save();

        $data = $this->makeRequest(['with' => ['user']])
            ->assertSuccessful()
            ->json('data');

        $invitationItem = collect($data)->firstWhere('has_account', true);

        tap($invitation->fresh(), function (Invitation $invitation) use ($invitationItem) {
            $this->assertEquals($invitation->id, $invitationItem['id']);
            $this->assertEquals($invitation->user_id, $invitationItem['user_id']);
        });
    }

    #[Test]
    public function it_wont_let_view_invitations_to_unauthenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->getJson(route('invitations.index', $attributes));
    }
}
