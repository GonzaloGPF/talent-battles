<?php

namespace Tests\Feature\BillingPortal;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewBillingPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
    }

    #[Test]
    public function it_let_user_view_billing_portal()
    {
        $this->mock(UserService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getRedirectToBillingPortal')
                ->once()
                ->with($this->user)
                ->andReturn(new RedirectResponse('someUrl'));
        });

        $this->makeRequest()
            ->assertSuccessful();
    }

    #[Test]
    public function non_authenticated_user_cannot_view_billing_portal()
    {
        $this->logout();

        $this->makeRequest()
            ->assertRedirect(route('welcome'));
    }

    private function makeRequest(): TestResponse
    {
        return $this->get(route('billing-portal.show'));
    }
}
