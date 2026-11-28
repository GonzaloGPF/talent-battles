<?php

namespace Tests\Feature\Stripe\Accounts;

use App\Jobs\Stripe\HandleAccountUpdated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Tests\TestCase;

/**
 * The account.updated webhook only says which account has changed: what is saved comes from Stripe.
 */
class AccountUpdatedWebhookTest extends TestCase
{
    use RefreshDatabase;

    private object $stripe;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.secret' => 'sk_test_key', 'cashier.secret' => 'sk_test_key']);

        $this->stripe = new class implements ClientInterface
        {
            public ?array $account = null;

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
            {
                return $this->account
                    ? [json_encode($this->account), 200, []]
                    : [json_encode(['error' => ['message' => 'No such account']]), 404, []];
            }
        };

        ApiRequestor::setHttpClient($this->stripe);
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(null);

        parent::tearDown();
    }

    #[Test]
    public function the_account_is_saved_as_stripe_returns_it()
    {
        $user = create(User::class, ['account' => $this->account(ready: false)]);
        $this->stripe->account = $this->account(ready: true);

        HandleAccountUpdated::dispatchSync($this->payload(['id' => 'acct_1']));

        $this->assertTrue($user->fresh()->isAccountReady());
    }

    #[Test]
    public function a_webhook_cannot_make_ready_an_account_that_stripe_has_not_approved()
    {
        $user = create(User::class, ['account' => $this->account(ready: false)]);
        $this->stripe->account = $this->account(ready: false);

        HandleAccountUpdated::dispatchSync($this->payload($this->account(ready: true)));

        $this->assertFalse($user->fresh()->isAccountReady());
    }

    #[Test]
    public function the_user_keeps_their_account_when_stripe_cannot_be_reached()
    {
        $user = create(User::class, ['account' => $this->account(ready: true)]);

        HandleAccountUpdated::dispatchSync($this->payload($this->account(ready: false)));

        $this->assertTrue($user->fresh()->isAccountReady());
    }

    private function account(bool $ready): array
    {
        return [
            'id' => 'acct_1',
            'object' => 'account',
            'details_submitted' => $ready,
            'charges_enabled' => $ready,
            'payouts_enabled' => $ready,
        ];
    }

    private function payload(array $account): array
    {
        return ['type' => 'account.updated', 'data' => ['object' => $account]];
    }
}
