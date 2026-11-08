<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendEmail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_register_a_new_user()
    {
        $this->makeRequest()
            ->assertSuccessful();

        /** @var User $user */
        $user = auth()->user();

        $this->assertEquals('test@mail.com', $user->email);
    }

    #[Test]
    public function new_users_start_with_enough_points_for_their_first_battle()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $points = User::firstWhere('email', 'test@mail.com')->points;

        $this->assertEquals(config('defaults.initial_points'), $points);
        $this->assertGreaterThanOrEqual(config('company.points.prices.create_battle'), $points);
    }

    #[Test]
    public function the_verification_token_has_64_random_characters()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertSame(64, strlen(User::firstWhere('email', 'test@mail.com')->token));
    }

    #[Test]
    public function when_registering_an_user_it_will_send_a_verification_email()
    {
        Queue::fake();

        $this->makeRequest()
            ->assertSuccessful();

        Queue::assertPushed(SendEmail::class);
    }

    #[Test]
    public function it_updates_invitation_when_registering_a_user_with_same_mail_of_invitation()
    {
        /** @var Invitation $invitation */
        $invitation = create(Invitation::class, [
            'created_by' => create(User::class)->id,
            'email' => 'some@email.com',
            'user_id' => null,
        ]);

        $this->makeRequest(['email' => 'some@email.com'])
            ->assertSuccessful();

        $this->assertEquals($invitation->fresh()->user_id, User::firstWhere(['email' => 'some@email.com'])->id);
    }

    #[Test]
    public function it_wont_let_register_user_if_password_is_smaller_than_8_characters()
    {
        $errorMessages = $this->makeRequest([
            'password' => 'Small1*',
            'password_confirmation' => 'Small1*',
        ])->assertUnprocessable()
            ->json('data.password');

        $this->assertEquals(trans('validation.min.string', [
            'attribute' => trans('validation.attributes.password'),
            'min' => 8,
        ]), $errorMessages[0]);
    }

    #[Test]
    public function it_wont_let_register_user_if_password_does_not_mix_uppercase_and_lowercase()
    {
        $errorMessages = $this->makeRequest([
            'password' => 'secret123**',
            'password_confirmation' => 'secret123**',
        ])->assertUnprocessable()
            ->json('data.password');

        $this->assertEquals(trans('validation.password.mixed', [
            'attribute' => trans('validation.attributes.password'),
        ]), $errorMessages[0]);
    }

    #[Test]
    public function it_wont_let_register_user_if_password_does_not_contain_any_number()
    {
        $errorMessages = $this->makeRequest([
            'password' => 'TopSecret!',
            'password_confirmation' => 'TopSecret!',
        ])->assertUnprocessable()
            ->json('data.password');

        $this->assertEquals(trans('validation.password.numbers', [
            'attribute' => trans('validation.attributes.password'),
        ]), $errorMessages[0]);
    }

    #[Test]
    public function it_wont_let_register_user_if_password_does_not_contain_any_symbol()
    {
        $errorMessages = $this->makeRequest([
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ])->assertUnprocessable()
            ->json('data.password');

        $this->assertEquals(trans('validation.password.symbols', [
            'attribute' => trans('validation.attributes.password'),
        ]), $errorMessages[0]);
    }

    #[Test]
    public function it_wont_let_register_a_logged_user()
    {
        $this->signIn();

        $this->makeRequest()
            ->assertRedirect();
    }

    #[Test]
    public function it_wont_let_register_users_if_dont_accept_terms_and_conditions()
    {
        $this->makeRequest(['terms_and_conditions' => false])
            ->assertUnprocessable();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('auth.register'), array_merge([
            'name' => 'Test',
            'email' => 'test@mail.com',
            'password' => 'Testing123**',
            'password_confirmation' => 'Testing123**',
            'terms_and_conditions' => true,
        ], $attributes));
    }
}
