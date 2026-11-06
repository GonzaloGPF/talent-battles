<?php

namespace Tests\Feature\Emails;

use App\Models\Email;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestEmailAccessTest extends TestCase
{
    use RefreshDatabase;

    private Email $email;

    protected function setUp(): void
    {
        parent::setUp();
        $this->email = create(Email::class);
    }

    #[Test]
    public function guests_cannot_list_emails()
    {
        $this->getJson(route('emails.index'))
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function guests_cannot_view_an_email()
    {
        $this->getJson(route('emails.show', $this->email))->assertNotFound();
    }

    #[Test]
    public function guests_cannot_render_an_email()
    {
        $this->getJson(route('emails.render', $this->email))->assertNotFound();
    }

    #[Test]
    public function guests_cannot_resend_an_email()
    {
        $this->putJson(route('emails.resend', $this->email))->assertNotFound();
    }
}
