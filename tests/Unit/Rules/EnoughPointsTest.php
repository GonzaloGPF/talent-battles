<?php

namespace Tests\Unit\Rules;

use App\Models\User;
use App\Rules\EnoughPoints;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnoughPointsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
    }

    #[Test]
    public function it_validates_user_has_enough_points(): void
    {
        $this->user->forceFill(['points' => 3])->saveQuietly();
        $this->assertFalse($this->isValid());

        $this->user->forceFill(['points' => 5])->saveQuietly();
        $this->assertTrue($this->isValid());
    }

    private function isValid(): bool
    {
        $validator = Validator::make(
            ['points' => $this->user->fresh()->points],
            ['points' => new EnoughPoints(5)]
        );

        return ! $validator->fails();
    }
}
