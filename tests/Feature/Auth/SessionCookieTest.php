<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SessionCookieTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function pages_keep_the_session_in_an_encrypted_cookie()
    {
        $sessionId = $this->sessionIdFrom($this->get('/'));

        $nextSessionId = $this->sessionIdFrom(
            $this->withCookie(config('session.cookie'), $sessionId)->get('/')
        );

        $this->assertSame($sessionId, $nextSessionId);
    }

    #[Test]
    public function the_spa_keeps_the_session_in_an_encrypted_cookie()
    {
        $headers = ['Referer' => config('app.url').'/'];
        $sessionId = $this->sessionIdFrom($this->getJson(route('auth.me'), $headers));

        $nextSessionId = $this->sessionIdFrom(
            $this->withCredentials()->withCookie(config('session.cookie'), $sessionId)->getJson(route('auth.me'), $headers)
        );

        $this->assertSame($sessionId, $nextSessionId);
    }

    /**
     * Reading the cookie decrypts it, so a session id sent in clear fails here.
     */
    private function sessionIdFrom(TestResponse $response): string
    {
        return $response->assertSuccessful()
            ->getCookie(config('session.cookie'))
            ->getValue();
    }
}
