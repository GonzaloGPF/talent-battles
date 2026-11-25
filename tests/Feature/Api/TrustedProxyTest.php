<?php

namespace Tests\Feature\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    private const VISITOR = '203.0.113.7';

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('api')->prefix('api')->get('testing/visitor', fn (Request $request) => [
            'ip' => $request->ip(),
            'host' => $request->getHost(),
            'secure' => $request->isSecure(),
        ]);
    }

    #[Test]
    public function behind_the_local_proxy_the_visitor_ip_comes_from_the_forwarded_header()
    {
        $this->fromAddress('127.0.0.1', ['X-Forwarded-For' => '198.51.100.9, '.self::VISITOR])
            ->assertJsonPath('ip', self::VISITOR);
    }

    #[Test]
    public function other_addresses_cannot_forge_the_visitor_ip()
    {
        $this->fromAddress('198.51.100.1', ['X-Forwarded-For' => self::VISITOR])
            ->assertJsonPath('ip', '198.51.100.1');
    }

    #[Test]
    public function the_host_and_scheme_are_not_taken_from_forwarded_headers()
    {
        $this->fromAddress('127.0.0.1', [
            'X-Forwarded-For' => self::VISITOR,
            'X-Forwarded-Host' => 'evil.example',
            'X-Forwarded-Proto' => 'https',
        ])
            ->assertJsonPath('host', 'localhost')
            ->assertJsonPath('secure', false);
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function fromAddress(string $address, array $headers): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $address])
            ->withHeaders($headers)
            ->getJson('http://localhost/api/testing/visitor')
            ->assertOk();
    }
}
