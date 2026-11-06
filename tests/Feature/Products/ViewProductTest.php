<?php

namespace Tests\Feature\Products;

use App\Models\File;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        Event::fake();
    }

    #[Test]
    public function when_viewing_an_specific_product_it_wont_show_file_if_user_is_not_the_owner()
    {
        /** @var User $someUser */
        $someUser = create(User::class);
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => $someUser->id,
        ]);
        create(File::class, [
            'created_by' => $someUser->id,
            'fileable_type' => Product::class,
            'fileable_id' => $product->id,
        ]);

        $this->signIn(create(User::class));

        $data = $this->getJson(route('products.show', [
            'product' => $product,
            'with' => 'file',
        ]))->assertSuccessful()
            ->json('data');

        $this->assertArrayNotHasKey('file', $data);
    }

    #[Test]
    public function when_viewing_multiple_products_it_wont_show_file_information_if_user_is_not_the_owner()
    {
        /** @var User $someUser */
        $someUser = create(User::class);
        /** @var Product $product */
        $product = create(Product::class, [
            'created_by' => $someUser->id,
        ]);
        create(File::class, [
            'created_by' => $someUser->id,
            'fileable_type' => Product::class,
            'fileable_id' => $product->id,
        ]);

        $this->signIn(create(User::class));

        $data = $this->getJson(route('products.index', ['with' => ['file']]))
            ->assertSuccessful()
            ->json('data');

        $this->assertArrayNotHasKey('file', $data[0]);
    }
}
