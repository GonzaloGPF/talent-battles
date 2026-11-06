<?php

namespace Products;

use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Track $track;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class, ['account' => [
            'details_submitted' => true,
            'charges_enabled' => true,
            'payouts_enabled' => true,
        ]]);
        $this->track = create(Track::class, ['created_by' => $this->user->id]);
        $this->product = create(Product::class, [
            'created_by' => $this->user->id,
            'orderable_type' => Track::class,
            'orderable_id' => $this->track->id,
        ]);
        $this->signIn($this->user);
    }

    #[Test]
    public function it_let_user_update_a_product()
    {
        $this->makeRequest(['name' => 'edited'])
            ->assertSuccessful();

        $this->assertEquals('edited', $this->product->fresh()->name);
    }

    #[Test]
    public function it_wont_let_modify_exclusive_product_if_it_was_already_sold()
    {
        $this->product
            ->forceFill([
                'exclusive' => true,
                'price' => 123,
                'last_sale' => today(),
            ])->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_modify_a_product_from_other_user()
    {
        $this->product
            ->forceFill(['created_by' => create(User::class)->id])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_remove_a_product_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('products.update', $this->product), $attributes);
    }
}
