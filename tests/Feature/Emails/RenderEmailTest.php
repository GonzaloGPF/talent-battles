<?php

namespace Tests\Feature\Emails;

use App\Models\Email;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RenderEmailTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Email $email;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->email = create(Email::class, ['to' => [$this->user->email]]);
    }

    #[Test]
    public function it_let_render_an_email()
    {
        $this->makeRequest()
            ->assertSuccessful();
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('emails.render', $this->email));
    }
}
