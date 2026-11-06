<?php

namespace Tests\Feature\Users;

use App\Enums\FileTypes;
use App\Enums\Roles;
use App\Enums\Skills;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateUserTest extends TestCase
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
    public function administrator_can_update_users()
    {
        $this->makeRequest(['name' => 'Test'])
            ->assertSuccessful();

        $this->assertEquals('Test', $this->user->fresh()->name);
    }

    #[Test]
    public function administrator_can_update_password_users()
    {
        $this->makeRequest([
            'password' => 'test123**',
            'password_confirmation' => 'test123**',
        ])->assertSuccessful();

        $this->assertTrue(Hash::check('test123**', $this->user->fresh()->password));
    }

    #[Test]
    public function non_administrator_cannot_update_another_user()
    {
        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest([], create(User::class))
            ->assertForbidden();
    }

    #[Test]
    public function user_can_update_password_its_own_password()
    {
        $user = create(User::class, ['role' => Roles::USER]);
        $this->signIn($user);

        $this->makeRequest([
            'password' => 'test123**',
            'password_confirmation' => 'test123**',
        ], $user)
            ->assertSuccessful();

        $this->assertTrue(Hash::check('test123**', $user->fresh()->password));
    }

    #[Test]
    public function user_can_update_itself()
    {
        $user = create(User::class, ['role' => Roles::USER]);
        $this->signIn($user);

        $this->makeRequest(['name' => 'Test'], $user)
            ->assertSuccessful();

        $this->assertEquals('Test', $user->fresh()->name);
    }

    #[Test]
    public function user_cannot_update_its_own_role()
    {
        $user = create(User::class, ['role' => Roles::USER]);
        $this->signIn($user);

        $this->makeRequest(['role' => Roles::ADMIN], $user)
            ->assertSuccessful();

        $this->assertEquals(Roles::USER, $user->fresh()->role);
    }

    #[Test]
    public function user_can_update_their_image()
    {
        $this->user->image()->save(create(File::class, ['file_type' => FileTypes::IMAGE]));

        $this->makeRequest(['image' => UploadedFile::fake()->image('test.webp')])
            ->assertSuccessful();

        $this->assertEquals('test', $this->user->fresh()->image->name);
    }

    #[Test]
    public function it_lets_user_change_locale()
    {
        $this->makeRequest([
            'locale' => 'da',
        ])->assertSuccessful();

        $this->assertEquals('da', $this->user->fresh()->locale);
    }

    #[Test]
    public function user_can_update_its_skills()
    {
        $this->user
            ->forceFill(['skills' => null])
            ->saveQuietly();

        $this->makeRequest([
            'skills' => [Skills::DRAWING->value, Skills::ICON_AND_LOGOS->value],
        ])->assertSuccessful();

        $this->assertEquals(collect([Skills::DRAWING, Skills::ICON_AND_LOGOS]), $this->user->fresh()->skills);
    }

    #[Test]
    public function when_updating_an_user_it_parses_notification_settings_correctly()
    {
        $this->makeRequest([
            'notification_settings' => [
                'like_received' => [
                    'mail' => '1',
                    'broadcast' => '0',
                ],
                'favorite_received' => [
                    'mail' => null,
                ],
            ],
        ])->assertSuccessful();

        tap($this->user->fresh(), function (User $user) {
            $this->assertTrue($user->notification_settings['like_received']['mail']);
            $this->assertFalse($user->notification_settings['like_received']['broadcast']);
            $this->assertFalse($user->notification_settings['favorite_received']['mail']);
            $this->assertFalse($user->notification_settings['favorite_received']['broadcast']);
        });
    }

    private function makeRequest(array $attributes = [], $user = null): TestResponse
    {
        return $this->putJson(route('users.update', $user ?? $this->user), $attributes);
    }
}
