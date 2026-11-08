<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class OAuthTest extends TestCase
{
    #[Test]
    #[TestWith(['google', 'accounts.google.com'])]
    #[TestWith(['facebook', 'facebook.com'])]
    public function it_redirects_to_the_oauth_provider(string $driver, string $providerHost)
    {
        $this->get("/$driver/redirect")
            ->assertRedirectContains($providerHost);
    }

    #[Test]
    #[TestWith(['/unknown/redirect'])]
    #[TestWith(['/unknown/callback'])]
    public function unknown_providers_are_not_found(string $url)
    {
        $this->get($url)
            ->assertNotFound();
    }
}
