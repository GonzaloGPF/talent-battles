<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateLocaleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lets_update_locale_to_authenticated_user()
    {
        $this->signIn(create(User::class, ['locale' => 'en']));

        $this->makeRequest(['locale' => 'es'])
            ->assertSuccessful();

        $this->assertEquals('es', auth()->user()->locale);
        $this->assertEquals('es', app()->getLocale());
    }

    #[Test]
    public function it_lets_update_locale_to_unauthenticated_users_too()
    {
        $this->makeRequest(['locale' => 'es'])
            ->assertSuccessful();

        $this->assertEquals('es', app()->getLocale());
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('locales.update'), $attributes);
    }
}
