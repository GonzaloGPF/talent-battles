<?php

namespace Tests\Feature\OrderLines;

use App\Models\OrderLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewOrderLineTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class);
        create(OrderLine::class, [
            'created_by' => $this->user->id,
        ], 3);
        $this->signIn($this->user);
    }

    #[Test]
    public function user_can_see_order_lines_created_by_himself()
    {
        create(OrderLine::class, [ // Should be ignored
            'created_by' => create(User::class)->id,
        ]);

        $this->assertCount(3, OrderLine::all());
    }

    public function unauthenticated_user_can_not_see_anonymous_order_lines()
    {
        auth()->logout();

        create(OrderLine::class, [ // Should be ignored
            'created_by' => null,
        ]);

        $this->assertCount(0, OrderLine::all());
    }
}
