<?php

namespace Tests\Feature\Stripe\PaymentMethods;

use App\Api\StripeApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeletePaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->user->setRelation('paymentMethods', collect([
            [
                'billing_details' => [
                    'address' => [
                        'city' => null,
                        'country' => null,
                        'line1' => null,
                        'line2' => null,
                        'postal_code' => '123456',
                        'state' => null,
                    ],
                    'email' => null,
                    'name' => null,
                    'phone' => null,
                ],
                'card' => [],
                'created' => '1623919838',
                'customer' => 'cus_JeBNFEfPlvi9ZY',
                'id' => 'pm_1J3GvqCJzKiFcYh4Tp9qHLhI',
                'livemode' => false,
                'metadata' => [],
                'object' => 'payment_method',
                'type' => 'card',
            ],
        ]));
    }

    #[Test]
    public function it_can_delete_a_payment_method()
    {
        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('deletePaymentMethod')
                ->once()
                ->withArgs(['pm_1J3GvqCJzKiFcYh4Tp9qHLhI']);

            $mock->shouldReceive('getPaymentMethods')
                ->once()
                ->withNoArgs()
                ->andReturn(collect());
        });

        $this->makeRequest($this->user->paymentMethods->first()['id'])
            ->assertSuccessful();
    }

    #[Test]
    public function it_can_delete_all_associated_payment_methods_if_0_is_passed()
    {
        $this->mock(StripeApi::class, function (MockInterface $mock) {
            $mock->shouldReceive('deletePaymentMethod')
                ->once()
                ->withArgs(['0']);

            $mock->shouldReceive('getPaymentMethods')
                ->once()
                ->withNoArgs()
                ->andReturn(collect());
        });

        $this->makeRequest('0')
            ->assertSuccessful();
    }

    #[Test]
    public function it_wont_let_delete_payment_methods_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(?string $paymentMethodId = '0'): TestResponse
    {
        return $this->deleteJson(route('stripe.payment_methods.destroy', $paymentMethodId));
    }
}
