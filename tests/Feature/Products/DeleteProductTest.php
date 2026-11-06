<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Track $track;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class, [
            'account' => [
                'details_submitted' => true,
                'payouts_enabled' => true,
                'charges_enabled' => true,
            ],
        ]);
        $this->track = create(Track::class, ['created_by' => $this->user->id]);
        $this->product = create(Product::class, [
            'created_by' => $this->user->id,
            'orderable_type' => Track::class,
            'orderable_id' => $this->track->id,
            'price' => 123,
            'exclusive' => false,
            'last_sale' => null,
        ]);
        $this->signIn($this->user);
    }

    #[Test]
    public function it_let_user_delete_a_product()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertNull($this->track->product);
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
    public function it_wont_let_delete_a_product_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('products.destroy', $this->product));
    }
}
