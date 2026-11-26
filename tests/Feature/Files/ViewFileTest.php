<?php

namespace Tests\Feature\Files;

use App\Enums\Roles;
use App\Models\File;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Report;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
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
        $this->signIn(create(User::class, ['role' => Roles::USER]));
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
    #[TestWith([OrderLine::class])]
    #[TestWith([Report::class])]
    public function a_private_file_is_forbidden_to_other_users(string $fileableType)
    {
        $file = $this->privateFile($fileableType, create(User::class));
        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->getJson(route('files.show', $file))
            ->assertForbidden();
    }

    #[Test]
    public function a_purchased_file_is_downloaded_by_its_buyer()
    {
        $buyer = create(User::class, ['role' => Roles::USER]);
        $file = $this->privateFile(OrderLine::class, $buyer);
        $this->signIn($buyer);

        $this->getJson(route('files.show', $file))
            ->assertSuccessful();
    }

    #[Test]
    public function a_purchased_file_is_downloaded_with_its_secret_link_without_signing_in()
    {
        $file = $this->privateFile(OrderLine::class, create(User::class));
        $this->logout();

        $this->getJson(route('files.show', ['file' => $file, 'secret' => 'TheSecret']))
            ->assertSuccessful();
    }

    #[Test]
    #[TestWith([[]])]
    #[TestWith([['secret' => 'WrongSecret']])]
    #[TestWith([['secret' => ['TheSecret']]])]
    public function a_purchased_file_is_forbidden_without_signing_in_or_its_secret(array $input)
    {
        $file = $this->privateFile(OrderLine::class, create(User::class));
        $this->logout();

        $this->getJson(route('files.show', ['file' => $file, ...$input]))
            ->assertForbidden();
    }

    #[Test]
    public function a_report_file_is_downloaded_by_an_admin()
    {
        $file = $this->privateFile(Report::class, create(User::class));

        $this->getJson(route('files.show', $file))
            ->assertSuccessful();
    }

    #[Test]
    public function a_public_file_is_downloaded_without_signing_in()
    {
        $track = create(Track::class);
        $file = create(File::class, [
            'path' => generateTestFile('someFile'),
            'fileable_type' => Track::class,
            'fileable_id' => $track->id,
        ]);
        $this->logout();

        $this->getJson(route('files.show', $file))
            ->assertSuccessful();
    }

    #[Test]
    public function the_file_list_leaves_out_the_private_files_of_other_users()
    {
        $user = create(User::class, ['role' => Roles::USER]);
        $track = create(Track::class);
        $publicFile = create(File::class, [
            'path' => generateTestFile('someFile'),
            'fileable_type' => Track::class,
            'fileable_id' => $track->id,
        ]);
        $myPurchase = $this->privateFile(OrderLine::class, $user);
        $this->privateFile(OrderLine::class, create(User::class));
        $this->signIn($user);

        $ids = $this->getJson(route('files.index', ['dont_paginate' => true, 'limit' => 1000]))
            ->assertSuccessful()
            ->json('data.*.id');

        $this->assertEqualsCanonicalizing([$publicFile->id, $myPurchase->id], $ids);
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

    /**
     * @param  class-string<OrderLine|Report>  $fileableType
     */
    private function privateFile(string $fileableType, User $owner): File
    {
        $fileable = create($fileableType, ['created_by' => $owner->id]);

        return create(File::class, [
            'path' => generateTestFile('someFile'),
            'fileable_type' => $fileableType,
            'fileable_id' => $fileable->id,
            'created_by' => $owner->id,
            'secret' => 'TheSecret',
        ]);
    }
}
