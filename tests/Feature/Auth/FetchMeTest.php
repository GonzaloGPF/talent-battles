<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FetchMeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
    }

    #[Test]
    public function it_can_fetch_logged_user_correctly()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $token = $this->user->tokens->first();
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertEquals($token->token, hash('sha256', $data['token']));
        $this->assertEquals($this->user->id, $data['user']['id']);
    }

    #[Test]
    public function when_fetching_logged_user_from_first_party_frontend_it_wont_generate_access_token()
    {
        $data = $this->makeRequest(['X-Requested-With' => 'XMLHttpRequest'])
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($this->user->id, $data['id']);
    }

    private function makeRequest(array $headers = []): TestResponse
    {
        return $this->getJson(route('auth.me'), $headers);
    }
}
