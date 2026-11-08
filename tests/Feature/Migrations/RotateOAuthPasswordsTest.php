<?php

namespace Tests\Feature\Migrations;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RotateOAuthPasswordsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function accounts_linked_to_an_oauth_provider_get_a_random_password()
    {
        $oauthUser = create(User::class, ['oauth_driver' => 'google', 'password' => '690f1a2b3c4d5', 'remember_token' => 'old']);
        $emailUser = create(User::class, ['oauth_driver' => null, 'password' => 'Secret123**', 'remember_token' => 'kept']);

        (require database_path('migrations/2026_11_08_190907_rotate_oauth_passwords.php'))->up();

        $oauthUser = $oauthUser->fresh();
        $this->assertFalse(Hash::check('690f1a2b3c4d5', $oauthUser->password));
        $this->assertNotSame('old', $oauthUser->remember_token);

        $emailUser = $emailUser->fresh();
        $this->assertTrue(Hash::check('Secret123**', $emailUser->password));
        $this->assertSame('kept', $emailUser->remember_token);
    }
}
