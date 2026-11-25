<?php

namespace Tests\Feature\Api;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private const REQUESTS_A_MINUTE = 300;

    #[Test]
    public function the_api_allows_300_requests_a_minute_and_then_says_when_to_try_again()
    {
        $this->freezeTime();
        $this->useUpTheLimit();

        $this->getJson(route('company.index'))
            ->assertTooManyRequests()
            ->assertHeader('Retry-After', 60);

        $this->travel(1)->minutes();

        $this->getJson(route('company.index'))
            ->assertSuccessful();
    }

    #[Test]
    public function signed_in_users_do_not_share_the_limit_of_their_ip()
    {
        $this->useUpTheLimit();

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->getJson(route('company.index'))
            ->assertSuccessful();
    }

    private function useUpTheLimit(): void
    {
        foreach (range(1, self::REQUESTS_A_MINUTE) as $ignored) {
            $this->getJson(route('company.index'))->assertSuccessful();
        }
    }
}
