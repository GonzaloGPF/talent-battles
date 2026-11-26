<?php

namespace Tests\Feature\Files;

use App\Enums\Roles;
use App\Models\File;
use App\Models\OrderLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewFileZipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->signIn();
    }

    #[Test]
    public function authenticated_user_can_download_a_file_zip()
    {
        /** @var File $file */
        $file = create(File::class, ['path' => generateTestFile('someFile')]);

        $this->getJson(route('files.zip', ['file_ids' => [$file->id]]))
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/zip');
    }

    #[Test]
    public function a_zip_with_a_purchased_file_of_another_user_is_forbidden()
    {
        $publicFile = create(File::class, ['path' => generateTestFile('someFile')]);
        $orderLine = create(OrderLine::class);
        $purchasedFile = create(File::class, [
            'path' => generateTestFile('someFile'),
            'fileable_type' => OrderLine::class,
            'fileable_id' => $orderLine->id,
            'created_by' => $orderLine->created_by,
        ]);
        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->getJson(route('files.zip', ['file_ids' => [$publicFile->id, $purchasedFile->id]]))
            ->assertForbidden();
    }
}
