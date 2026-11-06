<?php

namespace Products;

use App\Enums\ProductTypes;
use App\Models\Design;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Event::fake();
        $this->user = create(User::class, ['account' => [
            'details_submitted' => true,
            'charges_enabled' => true,
            'payouts_enabled' => true,
        ]]);
        $this->signIn($this->user);
    }

    #[Test]
    public function it_let_create_a_product_for_a_track()
    {
        /** @var Track $track */
        $track = create(Track::class, ['created_by' => $this->user->id]);

        $this->makeRequest([
            'model_type' => 'track',
            'model_id' => $track->id,
            'price' => 100,
            'exclusive' => true,
            'file' => UploadedFile::fake()->create('test.mp3'),
        ])->assertSuccessful();

        tap($track->product, function (Product $product) use ($track) {
            $this->assertEquals($track->name, $product->name);
            $this->assertEquals($track->description, $product->description);
            $this->assertEquals(ProductTypes::TRACK, $product->product_type);
            $this->assertEquals(100, $product->price);
            $this->assertTrue($product->exclusive);
            $this->assertEquals('test', $product->file->name);
        });
    }

    #[Test]
    public function it_let_create_a_product_for_a_design()
    {
        /** @var Design $design */
        $design = create(Design::class, ['created_by' => $this->user->id]);

        $this->makeRequest([
            'model_type' => 'design',
            'model_id' => $design->id,
            'price' => 100,
            'exclusive' => false,
            'file' => UploadedFile::fake()->image('test.webp'),
        ])->assertSuccessful();

        tap($design->product, function (Product $product) use ($design) {
            $this->assertEquals($design->name, $product->name);
            $this->assertEquals($design->description, $product->description);
            $this->assertEquals(ProductTypes::DESIGN, $product->product_type);
            $this->assertEquals(100, $product->price);
            $this->assertFalse($product->exclusive);
            $this->assertEquals('test', $product->file->name);
        });
    }

    #[Test]
    public function it_wont_let_create_a_product_if_user_has_not_account_ready()
    {
        $this->user
            ->forceFill(['account' => null])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_create_a_product_to_non_authenticated_user()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('products.store'), $attributes);
    }
}
