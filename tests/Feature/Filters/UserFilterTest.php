<?php

namespace Tests\Feature\Filters;

use App\Enums\Categories;
use App\Enums\Gender;
use App\Enums\Roles;
use App\Enums\Skills;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        create(User::class, [], 3); // Extra users
    }

    #[Test]
    public function it_can_filter_users_by_nick()
    {
        create(User::class, ['nick' => 'Test']);

        $data = User::filter(['nick' => 'test'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_users_by_nick_or_name()
    {
        create(User::class, ['nick' => 'Test']);
        create(User::class, ['name' => 'Test']);

        $data = User::filter(['nick_name' => 'test'])->get();

        $this->assertCount(2, $data);
    }

    #[Test]
    public function it_can_filter_users_by_role()
    {
        User::query()->update(['role' => Roles::USER]);

        create(User::class, ['role' => Roles::MANAGER]);

        $data = User::filter(['roles' => [Roles::MANAGER->value]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_users_by_gender()
    {
        User::query()->update(['gender' => Gender::MALE]);

        create(User::class, ['gender' => Gender::FEMALE]);

        $data = User::filter(['gender' => [Gender::FEMALE->value]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_users_by_verified()
    {
        User::query()->update(['verified_at' => null]);

        create(User::class, ['verified_at' => today()]);

        $data = User::filter(['verified' => true])->get();
        $this->assertCount(1, $data);

        $data = User::filter(['verified' => false])->get();
        $this->assertCount(3, $data);
    }

    #[Test]
    public function it_can_filter_users_by_email()
    {
        create(User::class, ['email' => 'test@mail.com']);

        $data = User::filter(['email' => 'test'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_tracks_by_skills()
    {
        User::query()->update(['skills' => null]);
        create(User::class, ['skills' => [Skills::DRAWING]]);

        $data = User::filter(['skills' => [Skills::DRAWING->value]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_tracks_by_categories()
    {
        User::query()->update(['categories' => null]);
        create(User::class, ['categories' => [Categories::DESIGNERS]]);

        $data = User::filter(['categories' => [Categories::DESIGNERS->value]])->get();

        $this->assertCount(1, $data);
    }
}
