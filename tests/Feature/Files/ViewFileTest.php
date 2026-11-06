<?php

namespace Tests\Feature\Files;

use App\Models\File;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewFileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
    }

    #[Test]
    public function authenticated_user_can_fetch_all_files_data()
    {
        $this->getJson(route('files.index'))
            ->assertSuccessful();
    }

    #[Test]
    public function authenticated_user_can_download_a_file()
    {
        $file = create(File::class, ['path' => generateTestFile('someFile')]);

        $this->getJson(route('files.show', $file))
            ->assertSuccessful();
    }

    #[Test]
    public function it_wont_let_view_file_of_a_product_if_user_is_not_the_owner()
    {
        /** @var User $someUser */
        $someUser = create(User::class);
        $product = create(Product::class, ['created_by' => $someUser->id]);
        $file = create(File::class, [
            'path' => generateTestFile('someFile'),
            'fileable_type' => Product::class,
            'fileable_id' => $product->id,
            'created_by' => $someUser->id,
            'secret' => 'SomeSecret',
        ]);

        $this->getJson(route('files.show', $file))
            ->assertForbidden();
    }

    #[Test]
    public function it_will_let_view_file_of_product_to_non_authenticated_user_if_specifies_correct_secret()
    {
        $this->logout();

        /** @var User $someUser */
        $someUser = create(User::class);
        $product = create(Product::class, ['created_by' => $someUser->id]);
        $file = create(File::class, [
            'path' => generateTestFile('someFile'),
            'fileable_type' => Product::class,
            'fileable_id' => $product->id,
            'created_by' => $someUser->id,
            'secret' => 'SomeSecret',
        ]);

        $this->getJson(route('files.show', [
            'file' => $file,
            'secret' => 'SomeSecret',
        ]))->assertSuccessful();
    }

    #[Test]
    public function if_wrong_file_path_it_will_return_a_not_found_image()
    {
        $file = make(File::class, [
            'path' => 'wrong/path/to/file.txt',
        ]);
        $file->saveQuietly();

        $this->get(route('files.show', $file))
            ->assertNotFound();
    }
}
