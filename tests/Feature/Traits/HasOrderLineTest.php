<?php

namespace Tests\Feature\Traits;

use App\Enums\PaymentStates;
use App\Enums\Roles;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class HasOrderLineTest extends TestCase
{
    use RefreshDatabase;

    private Track $track;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->track = create(Track::class);
    }

    #[Test]
    public function the_paid_purchase_of_the_signed_in_user_is_shown_as_bought()
    {
        $buyer = create(User::class, ['role' => Roles::USER]);
        $orderLine = $this->purchase(['created_by' => $buyer->id]);
        $this->signIn($buyer);

        $this->showTrack()
            ->assertJsonPath('data.is_bought', true)
            ->assertJsonPath('data.orderLine.id', $orderLine->id);
    }

    #[Test]
    public function the_purchase_of_another_user_is_neither_shown_nor_bought()
    {
        $this->purchase(['created_by' => create(User::class)->id]);
        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->showTrack()
            ->assertJsonPath('data.is_bought', false)
            ->assertJsonPath('data.orderLine', null);
    }

    #[Test]
    #[TestWith([PaymentStates::PENDING])]
    #[TestWith([PaymentStates::ERROR])]
    public function an_unpaid_purchase_is_not_bought(PaymentStates $paymentState)
    {
        $buyer = create(User::class, ['role' => Roles::USER]);
        $this->purchase(['created_by' => $buyer->id], $paymentState);
        $this->signIn($buyer);

        $this->showTrack()
            ->assertJsonPath('data.is_bought', false)
            ->assertJsonPath('data.orderLine', null);
    }

    #[Test]
    public function a_guest_sees_no_purchase_not_even_one_made_as_a_guest()
    {
        $this->purchase(['created_by' => null, 'guest_id' => 'a-guest']);

        $this->showTrack()
            ->assertJsonPath('data.is_bought', false)
            ->assertJsonPath('data.orderLine', null);
    }

    private function showTrack(): TestResponse
    {
        return $this->getJson(route('tracks.show', ['track' => $this->track->id, 'with' => ['orderLine']]))
            ->assertOk();
    }

    /**
     * @param  array<string, mixed>  $buyer  the buyer columns of the order: created_by and guest_id
     */
    private function purchase(array $buyer, PaymentStates $paymentState = PaymentStates::SUCCESS): OrderLine
    {
        $order = create(Order::class, ['guest_id' => null, ...$buyer, 'payment_state' => $paymentState]);

        return create(OrderLine::class, [
            'order_id' => $order->id,
            'created_by' => $order->created_by,
            'orderable_type' => Track::class,
            'orderable_id' => $this->track->id,
        ]);
    }
}
