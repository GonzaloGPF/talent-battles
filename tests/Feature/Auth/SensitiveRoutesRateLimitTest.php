<?php

namespace Tests\Feature\Auth;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SensitiveRoutesRateLimitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function sensitiveRoutes(): array
    {
        return [
            'register' => ['POST', 'auth.register', false],
            'forgot password' => ['POST', 'auth.password.forgot', false],
            'reset password' => ['POST', 'auth.password.reset', false],
            'update password' => ['PUT', 'auth.password.update', true],
            'contact' => ['POST', 'emails.contact', false],
        ];
    }

    #[Test]
    #[DataProvider('sensitiveRoutes')]
    public function sensitive_routes_allow_five_requests_a_minute(string $method, string $route, bool $signedIn)
    {
        $parameters = match ($route) {
            'auth.password.reset' => ['token' => 'any'],
            'auth.password.update' => ['user' => $this->signIn(create(User::class, ['role' => Roles::USER]))],
            default => [],
        };

        foreach (range(1, 5) as $ignored) {
            $this->assertNotSame(429, $this->json($method, route($route, $parameters))->status());
        }

        $this->json($method, route($route, $parameters))
            ->assertTooManyRequests();
    }

    #[Test]
    public function a_limited_request_says_when_to_try_again()
    {
        foreach (range(1, 5) as $ignored) {
            $this->postJson(route('auth.password.forgot'));
        }

        $response = $this->postJson(route('auth.password.forgot'))
            ->assertTooManyRequests()
            ->assertHeader('Retry-After', 60);

        $this->assertEquals(trans('exceptions.too_many_attempts', ['seconds' => 60]), $response->json('message'));
    }
}
