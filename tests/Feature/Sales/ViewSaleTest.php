<?php

namespace Tests\Feature\Sales;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewSaleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        create(Sale::class, [], 3);
    }

    #[Test]
    public function it_let_user_view_its_sales()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(3, $data);
    }

    #[Test]
    public function it_does_not_let_user_view_sales_from_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        create(Sale::class, [
            'created_by' => $otherUser->id,
        ]);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(3, $data);
    }

    #[Test]
    public function non_authenticated_user_can_not_view_sales()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('sales.index'));
    }
}
