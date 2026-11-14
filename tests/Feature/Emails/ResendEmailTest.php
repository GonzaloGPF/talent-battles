<?php

namespace Tests\Feature\Emails;

use App\Enums\Roles;
use App\Jobs\SendEmail;
use App\Models\Email;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResendEmailTest extends TestCase
{
    use RefreshDatabase;

    private Email $email;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->signIn();
        $this->email = create(Email::class, ['sent' => null]);
    }

    #[Test]
    public function it_let_admin_user_resend_an_email()
    {
        $this->makeRequest()
            ->assertSuccessful();

        Queue::assertPushed(SendEmail::class);
    }

    #[Test]
    public function it_let_user_resend_own_email()
    {
        /** @var User $user */
        $user = create(User::class, ['role' => Roles::USER]);

        $this->email
            ->forceFill([
                'created_by' => $user->id,
                'to' => [$user->email],
                'retries' => 0,
            ])->saveQuietly();

        $this->makeRequest()
            ->assertSuccessful();

        Queue::assertPushed(SendEmail::class);

        $this->assertEquals(1, $this->email->fresh()->retries);
    }

    #[Test]
    public function resending_an_email_someone_else_created_keeps_who_is_signed_in()
    {
        $user = create(User::class, ['role' => Roles::USER]);
        $this->email
            ->forceFill([
                'created_by' => create(User::class)->id,
                'to' => [$user->email],
                'retries' => 0,
            ])->saveQuietly();

        $this->signIn($user);

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertAuthenticatedAs($user, 'web');
    }

    #[Test]
    public function non_admin_users_cannot_resend_emails_if_it_has_more_than_3_retries()
    {
        /** @var User $user */
        $user = create(User::class, ['role' => Roles::USER]);

        $this->email
            ->forceFill([
                'created_by' => $user->id,
                'to' => [$user->email],
                'retries' => 3,
            ])->saveQuietly();

        $this->signIn($user);

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(): TestResponse
    {
        return $this->putJson(route('emails.resend', $this->email));
    }
}
