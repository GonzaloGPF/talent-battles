<?php

namespace Tests\Feature\Carts;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewCartTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Cart $chart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->chart = create(Cart::class, ['created_by' => $this->user->id]);
    }

    #[Test]
    public function user_can_fetch_its_chart()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($this->chart->id, $data['id']);
    }

    #[Test]
    public function when_fetching_chart_it_will_return_an_empty_response_if_user_has_no_chart()
    {
        Cart::query()->delete();

        $this->makeRequest()
            ->assertNoContent();
    }

    #[Test]
    public function when_fetching_chart_it_will_return_an_empty_response_if_unauthenticated_user_has_no_chart()
    {
        $this->logout();

        Cart::query()->delete();

        $this->makeRequest()
            ->assertNoContent();
    }

    #[Test]
    public function when_fetching_a_chart_it_wont_fetch_chart_from_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        /** @var Cart $otherChart */
        $otherChart = create(Cart::class, ['created_by' => $otherUser->id]);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($this->chart->id, $data['id']);
        $this->assertNotEquals($otherChart->id, $data['id']);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('carts.show'));
    }
}
