<?php

namespace Tests\Feature\Orders;

use App\Api\StripeApi;
use App\Enums\PaymentStates;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Stripe\PaymentIntent;
use Tests\TestCase;

class OrderCompleteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class);
        $this->order = create(Order::class, [
            'created_by' => $this->user->id,
            'payment_state' => PaymentStates::PENDING,
            'meta' => [
                'payment_intent_id' => 'someId',
            ],
        ]);
        $this->signIn($this->user);
    }

    #[Test]
    public function it_let_complete_a_pending_order()
    {
        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('confirmPaymentIntent')
                ->once()
                ->with('someId', URL::signedRoute('orders.success', ['order' => $this->order]))
                ->andReturn(new PaymentIntent);
        });

        $this->makeRequest()
            ->assertSuccessful();
    }

    #[Test]
    public function it_wont_let_complete_an_order_if_it_has_wrong_state()
    {
        $this->order
            ->forceFill(['payment_state' => PaymentStates::SUCCESS])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();

        $this->order
            ->forceFill(['payment_state' => PaymentStates::ERROR])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_resume_an_order_that_belongs_to_another_user()
    {
        $this->order
            ->forceFill(['created_by' => create(User::class)->id])
            ->saveQuietly();

        $this->makeRequest()
            ->assertNotFound();
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('orders.complete', $this->order));
    }
}
