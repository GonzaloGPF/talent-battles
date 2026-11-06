<?php

namespace Tests\Feature\Filters;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvitationFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(Invitation::class, [], 3);
    }

    #[Test]
    public function it_can_filter_invitations_by_email()
    {
        create(Invitation::class, ['email' => 'test']);

        $invitations = Invitation::filter(['email' => 'test'])->get();

        $this->assertCount(1, $invitations);
    }

    #[Test]
    public function it_can_filter_invitations_by_accepted()
    {
        Invitation::query()->update(['user_id' => null]);

        create(Invitation::class, ['user_id' => create(User::class)->id]);

        $invitations = Invitation::filter(['accepted' => true])->get();

        $this->assertCount(1, $invitations);

        $invitations = Invitation::filter(['accepted' => false])->get();

        $this->assertCount(3, $invitations);
    }

    #[Test]
    public function it_can_filter_invitations_by_has_account()
    {
        Invitation::query()->update(['user_id' => null]);

        /** @var User $userWithAccount */
        $userWithAccount = create(User::class, ['account' => 'someData']);
        create(Invitation::class, ['user_id' => $userWithAccount->id]);

        /** @var User $userWithoutAccount */
        $userWithoutAccount = create(User::class, ['account' => null]);
        create(Invitation::class, ['user_id' => $userWithoutAccount->id]);

        $invitations = Invitation::filter(['has_account' => true])->get();

        $this->assertCount(1, $invitations);

        $invitations = Invitation::filter(['has_account' => false])->get();

        $this->assertCount(1, $invitations);
    }
}
