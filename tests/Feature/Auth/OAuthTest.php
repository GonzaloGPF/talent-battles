<?php

namespace Tests\Feature\Auth;

use App\Enums\EmailTypes;
use App\Jobs\SendEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class OAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[TestWith(['google', 'accounts.google.com'])]
    #[TestWith(['facebook', 'facebook.com'])]
    public function it_redirects_to_the_oauth_provider(string $driver, string $providerHost)
    {
        $this->get("/$driver/redirect")
            ->assertRedirectContains($providerHost);
    }

    #[Test]
    #[TestWith(['/unknown/redirect'])]
    #[TestWith(['/unknown/callback'])]
    public function unknown_providers_are_not_found(string $url)
    {
        $this->get($url)
            ->assertNotFound();
    }

    #[Test]
    public function it_remembers_the_page_to_come_back_to()
    {
        $this->get('/google/redirect?redirect=/battles/14')
            ->assertSessionHas('url.intended', url('/battles/14'));
    }

    #[Test]
    #[TestWith(['//evil.example'])]
    #[TestWith(['/\\evil.example'])]
    #[TestWith(['https://evil.example'])]
    public function it_does_not_come_back_to_another_site(string $redirect)
    {
        $this->get('/google/redirect?redirect='.urlencode($redirect))
            ->assertSessionMissing('url.intended');
    }

    #[Test]
    public function a_login_without_a_page_forgets_the_previous_one()
    {
        $this->withSession(['url.intended' => url('/battles/14')])
            ->get('/google/redirect')
            ->assertSessionMissing('url.intended');
    }

    #[Test]
    public function the_callback_creates_the_account_and_sends_the_email_to_set_a_password()
    {
        Queue::fake([SendEmail::class]);
        Socialite::fake('google', $this->oAuthUser('new@email.com'));

        $this->withSession(['url.intended' => url('/battles/14')])
            ->get('/google/callback')
            ->assertRedirect('/battles/14');

        $user = User::firstWhere('email', 'new@email.com');
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertSame('google', $user->oauth_driver);
        $this->assertNotNull($user->verified_at);
        Queue::assertPushed(SendEmail::class, function (SendEmail $job) {
            return $job->email->email_type === EmailTypes::RESET_PASSWORD
                && $job->email->meta['is_new'] === true
                && $job->email->to === ['new@email.com'];
        });
    }

    #[Test]
    public function the_callback_logs_in_an_existing_account_without_sending_emails()
    {
        Queue::fake([SendEmail::class]);
        Socialite::fake('google', $this->oAuthUser('existing@email.com'));
        $existingUser = create(User::class, ['email' => 'existing@email.com', 'verified_at' => null]);

        $this->get('/google/callback')
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($existingUser, 'web');
        $this->assertSame('google', $existingUser->fresh()->oauth_driver);
        $this->assertNotNull($existingUser->fresh()->verified_at);
        Queue::assertNotPushed(SendEmail::class);
    }

    #[Test]
    public function the_callback_starts_a_new_session()
    {
        Socialite::fake('google', $this->oAuthUser('new@email.com'));
        $this->startSession();
        $guestSessionId = session()->getId();

        $this->get('/google/callback');

        $this->assertNotSame($guestSessionId, session()->getId());
    }

    #[Test]
    public function a_denied_login_comes_back_with_a_message()
    {
        Socialite::fake('google', fn () => $this->fail('The provider must not be asked for the user'));

        $this->withSession(['url.intended' => url('/battles/14')])
            ->get('/google/callback?error=access_denied')
            ->assertRedirect('/battles/14')
            ->assertSessionHas('message', ['message' => trans('help.oauth_failed'), 'type' => 'error']);

        $this->assertGuest('web');
    }

    #[Test]
    public function an_expired_login_comes_back_with_a_message()
    {
        Socialite::fake('google', fn () => throw new InvalidStateException);

        $this->get('/google/callback')
            ->assertRedirect('/')
            ->assertSessionHas('message', ['message' => trans('help.oauth_failed'), 'type' => 'error']);

        $this->assertGuest('web');
    }

    #[Test]
    public function the_next_page_shows_the_message_once()
    {
        Socialite::fake('google', fn () => throw new InvalidStateException);

        $this->get('/google/callback');

        $this->get('/')->assertSee(trans('help.oauth_failed'));
        $this->get('/')->assertDontSee(trans('help.oauth_failed'));
    }

    #[Test]
    public function an_account_without_email_is_not_created()
    {
        Socialite::fake('facebook', $this->oAuthUser(null));

        $this->get('/facebook/callback')
            ->assertRedirect('/')
            ->assertSessionHas('message', ['message' => trans('help.oauth_failed'), 'type' => 'error']);

        $this->assertGuest('web');
        $this->assertDatabaseMissing('users', ['oauth_id' => '123456']);
    }

    private function oAuthUser(?string $email): SocialiteUser
    {
        return (new SocialiteUser)->map([
            'id' => '123456',
            'name' => 'OAuth User',
            'nickname' => 'oauth',
            'email' => $email,
        ]);
    }
}
