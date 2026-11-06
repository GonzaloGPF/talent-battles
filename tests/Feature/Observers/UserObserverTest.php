<?php

namespace Tests\Feature\Observers;

use App\Models\User;
use App\Observers\UserObserver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserObserverTest extends TestCase
{
    private UserObserver $observer;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->observer = resolve(UserObserver::class);
        $this->user = new User;
    }

    #[Test]
    public function it_sets_to_null_verified_at_when_updating_email()
    {
        $this->user->verified_at = now();
        $this->user->email = 'some@mail.com';

        $this->observer->updating($this->user);

        $this->assertNull($this->user->verified_at);
    }

    #[Test]
    public function it_sets_initial_points_when_creating()
    {
        $this->observer->creating($this->user);

        $this->assertEquals(config('defaults.initial_points'), $this->user->points);
    }
}
