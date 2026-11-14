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
use PHPUnit\Framework\Attributes\TestWith;
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
        $this->signIn();
        User::query()->update(['role' => Roles::USER]);

        create(User::class, ['role' => Roles::MANAGER]);

        $data = User::filter(['roles' => [Roles::MANAGER->value]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_users_by_gender()
    {
        $this->signIn();
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
        $this->signIn();
        create(User::class, ['email' => 'test@mail.com']);

        $data = User::filter(['email' => 'test@mail'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    #[TestWith([['email' => 'private']])]
    #[TestWith([['not_email' => 'zz-private@mail.com']])]
    #[TestWith([['min_birthdate' => '2000-01-01']])]
    #[TestWith([['gender' => ['female']]])]
    #[TestWith([['roles' => ['manager']]])]
    #[TestWith([['sort' => 'email|asc']])]
    public function only_admins_can_filter_or_sort_users_by_private_fields(array $input)
    {
        $admin = User::factory()->admin()->create();
        User::query()->update(['gender' => Gender::MALE, 'birthdate' => '1980-01-01']);
        User::query()->whereKeyNot($admin->id)->update(['role' => Roles::USER]);
        create(User::class, [
            'email' => 'zz-private@mail.com',
            'birthdate' => '2005-01-01',
            'gender' => Gender::FEMALE,
            'role' => Roles::MANAGER,
            'created_at' => now()->addMinute(),
        ]);
        $everyone = User::filter([])->pluck('id')->all();

        $this->assertSame($everyone, User::filter($input)->pluck('id')->all());

        $this->actingAs($admin);

        $this->assertNotSame($everyone, User::filter($input)->pluck('id')->all());
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
