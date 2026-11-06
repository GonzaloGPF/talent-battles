<?php

namespace Tests\Feature\Emails;

use App\Enums\Roles;
use App\Models\Email;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        create(Email::class, [], 3);
    }

    #[Test]
    public function admin_can_view_email()
    {
        $data = $this->getJson(route('emails.index'))
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(3, $data);
    }

    #[Test]
    public function admin_can_view_specific_email()
    {
        /** @var Email $email */
        $email = create(Email::class);

        $data = $this->getJson(route('emails.show', $email))
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($email->to, $data['to']);
    }

    #[Test]
    public function it_wont_let_view_emails_to_user_if_its_not_for_him()
    {
        /** @var User $user */
        $user = create(User::class, [
            'role' => Roles::USER,
            'email' => 'some@email.com',
        ]);

        $email = create(Email::class, [
            'created_by' => create(User::class)->id,
            'to' => ['other@email.com'],
        ]);

        $this->signIn($user);

        $this->getJson(route('emails.show', $email))
            ->assertNotFound();
    }

    #[Test]
    public function it_let_view_emails_to_other_user_if_is_created_by_him()
    {
        /** @var User $user */
        $user = create(User::class, [
            'role' => Roles::USER,
            'email' => 'some@email.com',
        ]);

        $email = create(Email::class, [
            'created_by' => $user->id,
            'to' => ['other@email.com'],
        ]);

        $this->signIn($user);

        $data = $this->getJson(route('emails.show', $email))
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($email->to, $data['to']);
    }
}
