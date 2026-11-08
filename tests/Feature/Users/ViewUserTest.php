<?php

namespace Tests\Feature\Users;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        create(User::class, [], 3);
    }

    #[Test]
    public function it_let_fetch_specific_user()
    {
        $user = create(User::class);

        $data = $this->getJson(route('users.show', $user))
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($user->id, $data['id']);
    }

    #[Test]
    public function it_let_fetch_users()
    {
        $data = $this->getJson(route('users.index'))
            ->json('data');

        $this->assertCount(3, $data);
    }

    #[Test]
    public function the_address_of_a_user_is_not_public()
    {
        $user = create(User::class);

        $this->getJson(route('users.show', $user))
            ->assertSuccessful()
            ->assertJsonMissingPath('data.address');

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->getJson(route('users.show', $user))
            ->assertSuccessful()
            ->assertJsonMissingPath('data.address');
    }

    #[Test]
    public function users_can_see_their_own_address()
    {
        $user = $this->signIn(create(User::class, ['role' => Roles::USER]));

        $address = $this->getJson(route('users.show', $user))
            ->json('data.address');

        $this->assertEquals($user->address, $address);
    }

    #[Test]
    public function admins_can_see_the_address_of_any_user()
    {
        $this->signIn();
        $user = create(User::class);

        $address = $this->getJson(route('users.show', $user))
            ->json('data.address');

        $this->assertEquals($user->address, $address);
    }
}
