<?php

namespace Tests\Feature\Auth;

use App\Enums\EmailTypes;
use App\Jobs\SendEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class);
    }

    #[Test]
    public function it_lets_user_forgot_password()
    {
        Queue::fake();

        $this->postJson(route('auth.password.forgot'), ['email' => $this->user->email])
            ->assertSuccessful();

        Queue::assertPushed(SendEmail::class, function (SendEmail $job) {
            return $job->email->email_type === EmailTypes::RESET_PASSWORD;
        });

        tap($this->user->fresh(), function (User $user) {
            $this->assertNotNull($user->token);
        });
    }

    #[Test]
    public function an_unknown_email_gets_the_same_answer_and_no_email_is_sent()
    {
        Queue::fake();

        $unknownEmail = $this->postJson(route('auth.password.forgot'), ['email' => 'some@email.com'])
            ->assertSuccessful();
        $knownEmail = $this->postJson(route('auth.password.forgot'), ['email' => $this->user->email])
            ->assertSuccessful();

        $this->assertSame($knownEmail->json(), $unknownEmail->json());
        Queue::assertPushedTimes(SendEmail::class, 1);
    }

    #[Test]
    public function reset_tokens_are_64_random_characters()
    {
        Queue::fake();

        $this->postJson(route('auth.password.forgot'), ['email' => $this->user->email]);
        $firstToken = $this->user->fresh()->token;
        $this->postJson(route('auth.password.forgot'), ['email' => $this->user->email]);
        $secondToken = $this->user->fresh()->token;

        $this->assertSame(64, strlen($firstToken));
        $this->assertNotSame($firstToken, $secondToken);
    }

    #[Test]
    public function it_lets_user_reset_password()
    {
        $token = 'someToken';

        $this->user->token = $token;
        $this->user->saveQuietly();

        $this->postJson(route('auth.password.reset', $token), [
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSuccessful();

        tap($this->user->fresh(), function (User $user) {
            $this->assertNull($user->token);
            $this->assertNotNull($user->verified_at);
            $this->assertTrue(Hash::check('secret123', $user->password));
        });
    }

    #[Test]
    public function it_wont_let_reset_password_with_a_wrong_token()
    {
        $this->user->token = null;
        $this->user->saveQuietly();

        $this->postJson(route('auth.password.reset', 'Wrong Token'), [
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertForbidden();
    }

    #[Test]
    public function it_let_users_update_its_password()
    {
        $this->signIn($this->user);

        $this->user->forceFill([
            'password' => 'somePassword',
        ])->saveQuietly();

        $this->putJson(route('auth.password.update', $this->user), [
            'current_password' => 'somePassword',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSuccessful();
    }

    #[Test]
    public function it_wont_let_user_update_its_password_if_given_password_is_incorrect()
    {
        $this->signIn($this->user);

        $this->user->forceFill([
            'password' => 'somePassword',
        ])->saveQuietly();

        $this->putJson(route('auth.password.update', $this->user), [
            'current_password' => 'someWrongPassword',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_user_non_authenticated_update_password()
    {
        $this->putJson(route('auth.password.update', $this->user), [])
            ->assertUnauthorized();
    }
}
