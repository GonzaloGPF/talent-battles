<?php

namespace Tests\Feature\Migrations;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RotateUserTokensTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function every_pending_token_is_replaced_by_a_random_one()
    {
        $userWithToken = create(User::class, ['token' => uniqid()]);
        $userWithoutToken = create(User::class, ['token' => null]);
        $oldToken = $userWithToken->token;

        (require database_path('migrations/2026_11_08_184621_rotate_user_tokens.php'))->up();

        $newToken = $userWithToken->fresh()->token;
        $this->assertSame(64, strlen($newToken));
        $this->assertNotSame($oldToken, $newToken);
        $this->assertNull($userWithoutToken->fresh()->token);
    }
}
