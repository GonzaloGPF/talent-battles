<?php

namespace Tests\Feature\Users;

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
}
