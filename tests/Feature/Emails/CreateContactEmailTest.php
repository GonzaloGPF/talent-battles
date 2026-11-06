<?php

namespace Tests\Feature\Emails;

use App\Enums\EmailTypes;
use App\Enums\Roles;
use App\Jobs\SendEmail;
use App\Models\Email;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateContactEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    #[Test]
    public function it_let_authenticated_users_to_send_a_contact_email_to_administrators()
    {
        /** @var User $user */
        $user = create(User::class, ['role' => Roles::USER]);

        $this->signIn($user);

        $this->makeRequest([
            'message' => 'Some message',
        ])->assertSuccessful();

        $email = Email::withoutGlobalScopes()->first();

        $this->assertEquals(EmailTypes::CONTACT_EMAIL, $email->email_type);
        $this->assertInArray('some@mail.com', $email->to);
        $this->assertEquals($user->email, $email->meta['email']);
        $this->assertEquals('Some message', $email->meta['message']);

        Queue::assertPushed(SendEmail::class);
    }

    #[Test]
    public function it_let_non_authenticated_users_to_send_a_contact_email_to_administrators()
    {
        $this->makeRequest([
            'message' => 'Some message',
            'email' => 'some@email.com',
        ])->assertSuccessful();

        $email = Email::withoutGlobalScopes()->first();

        $this->assertEquals(EmailTypes::CONTACT_EMAIL, $email->email_type);
        $this->assertInArray('some@mail.com', $email->to);
        $this->assertEquals('some@email.com', $email->meta['email']);
        $this->assertEquals('Some message', $email->meta['message']);

        Queue::assertPushed(SendEmail::class);
    }

    private function makeRequest(array $attributes): TestResponse
    {
        return $this->postJson(route('emails.contact'), $attributes);
    }
}
