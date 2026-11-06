<?php

namespace Tests\Feature\Filters;

use App\Models\OrderLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderLineFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buyer = create(User::class);
        $this->seller = create(User::class);
        create(OrderLine::class, [
            'created_by' => $this->buyer->id,
            'seller_id' => $this->seller->id,
        ], 3);
        $this->signIn($this->buyer);
    }

    #[Test]
    public function it_can_filter_order_lines_by_seller_id()
    {
        /** @var User $newSeller */
        $newSeller = create(User::class);

        create(OrderLine::class, [
            'created_by' => $this->buyer->id,
            'seller_id' => $newSeller->id,
        ]);

        $data = OrderLine::filter(['seller' => [$newSeller->id]])->get();

        $this->assertCount(1, $data);
    }
}
