<?php

namespace Products;

use App\Enums\FileTypes;
use App\Enums\ProductTypes;
use App\Models\Design;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateDesignProductTest extends TestCase
{
    use RefreshDatabase;

    private Design $design;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->user = create(User::class, ['account' => [
            'details_submitted' => true,
            'charges_enabled' => true,
            'payouts_enabled' => true,
        ]]);
        $this->design = create(Design::class, [
            'created_by' => $this->user->id,
            'name' => 'Some Name',
            'description' => 'Some Description',
        ]);
        $this->signIn($this->user);
    }

    #[Test]
    public function users_can_create_product_for_a_design()
    {
        $this->makeRequest()
            ->assertSuccessful();

        /** @var Product $product */
        $product = $this->design->product;

        $this->assertEquals(100, $product->price);
        $this->assertEquals('Some Name', $product->name);
        $this->assertEquals('Some Description', $product->description);
        $this->assertEquals(ProductTypes::DESIGN, $product->product_type);

        $file = $product->file;
        $this->assertEquals(FileTypes::IMAGE, $file->file_type);
        $this->assertEquals('test', $file->name);

    }

    #[Test]
    public function it_wont_let_create_a_product_for_a_design_with_wrong_mime()
    {
        $this->makeRequest([
            'file' => UploadedFile::fake()->create('test.txt'),
        ])->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_product_for_a_design_that_already_has_a_product()
    {
        create(Product::class, [
            'created_by' => $this->user->id,
            'orderable_type' => Design::class,
            'orderable_id' => $this->design->id,
        ]);

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_product_for_a_design_of_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        $this->design->created_by = $otherUser->id;
        $this->design->save();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('products.store'), array_merge([
            'file' => UploadedFile::fake()->image('test.webp'),
            'price' => 100,
            'model_type' => Design::class,
            'model_id' => $this->design->id,
            'product_type' => ProductTypes::DESIGN->value,
        ], $attributes));
    }
}
