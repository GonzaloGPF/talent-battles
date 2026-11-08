<?php

namespace Tests\Feature\Users;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
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
    #[TestWith(['address'])]
    #[TestWith(['role'])]
    public function a_private_field_of_a_user_is_not_public(string $field)
    {
        $user = create(User::class);

        $this->getJson(route('users.show', $user))
            ->assertSuccessful()
            ->assertJsonMissingPath("data.$field");

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->getJson(route('users.show', $user))
            ->assertSuccessful()
            ->assertJsonMissingPath("data.$field");
    }

    #[Test]
    #[TestWith(['address'])]
    #[TestWith(['role'])]
    public function users_can_see_their_own_private_fields(string $field)
    {
        $user = $this->signIn(create(User::class, ['role' => Roles::USER]));

        $value = $this->getJson(route('users.show', $user))
            ->json("data.$field");

        $this->assertEquals($user->fresh()->toArray()[$field], $value);
    }

    #[Test]
    #[TestWith(['address'])]
    #[TestWith(['role'])]
    public function admins_can_see_the_private_fields_of_any_user(string $field)
    {
        $this->signIn();
        $user = create(User::class);

        $value = $this->getJson(route('users.show', $user))
            ->json("data.$field");

        $this->assertEquals($user->fresh()->toArray()[$field], $value);
    }
}
