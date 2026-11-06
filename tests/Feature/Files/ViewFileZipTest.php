<?php

namespace Tests\Feature\Files;

use App\Models\File;
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
}
