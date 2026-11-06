<?php

namespace Tests\Feature\Orders;

use App\Enums\Roles;
use App\Models\File;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Order $order;

    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();
        File::flushEventListeners();
        $this->seller = create(User::class);
        $this->user = create(User::class, ['role' => Roles::USER]);
        $this->order = create(Order::class, [
            'created_by' => $this->user->id,
        ]);

        $this->signIn($this->user);
    }

    #[Test]
    public function it_let_view_own_orders_to_a_user()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_wont_let_user_view_orders_from_other_users()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        create(Order::class, [
            'created_by' => $otherUser->id,
        ]);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    //    /** @test */
    //    public function admin_user_can_view_all_orders()
    //    {
    //        create(Order::class, ['created_by' => create(User::class)->id]);
    //
    //        $this->signIn(create(User::class, ['role' => Roles::ADMIN]));
    //
    //        $data = $this->makeRequest(['view_all' => true])
    //            ->assertSuccessful()
    //            ->json('data');
    //
    //        $this->assertCount(2, $data);
    //    }

    #[Test]
    public function it_let_user_view_order_lines_if_are_created_by_themself()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        create(OrderLine::class, [
            'order_id' => create(Order::class, ['created_by' => $otherUser->id]),
            'created_by' => $otherUser->id,
            'seller_id' => $this->seller->id,
        ]); // Should be ignored

        create(OrderLine::class, [
            'created_by' => $this->user->id,
            'seller_id' => $this->seller->id,
        ]); // Should be considered

        $data = $this->makeRequest(['counts' => ['orderLines']])
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals(1, $data[0]['order_lines_count']);
    }

    #[Test]
    public function it_let_view_orders_to_non_authenticated_user_if_it_has_guest_id_is_session()
    {
        $this->logout();

        $this->order->forceFill([
            'created_by' => null,
            'guest_id' => CartService::getOrCreateGuestId(),
        ])->saveQuietly();

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_wont_let_view_any_order_to_non_authenticated_user()
    {
        $this->logout();

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(0, $data);
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->getJson(route('orders.index', $attributes));
    }
}
