<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessImageJob;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessImageJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_stores_a_converted_image_as_webp_with_the_webp_mime()
    {
        $file = $this->createImageFile(UploadedFile::fake()->image('cover.png')->store('files'), 'image/png');

        ProcessImageJob::dispatchSync($file);

        $file->refresh();
        $this->assertStringEndsWith('.webp', $file->path);
        $this->assertSame('image/webp', $file->mime);
        $this->assertSame('image/webp', Storage::mimeType($file->path));
    }

    #[Test]
    public function it_keeps_the_path_and_mime_of_an_image_that_cannot_be_converted()
    {
        Storage::put('files/broken.png', 'not an image');
        $file = $this->createImageFile('files/broken.png', 'image/png');

        ProcessImageJob::dispatchSync($file);

        $file->refresh();
        $this->assertSame('files/broken.png', $file->path);
        $this->assertSame('image/png', $file->mime);
    }

    private function createImageFile(string $path, string $mime): File
    {
        /** @var File $file */
        $file = make(File::class, [
            'fileable_type' => User::class,
            'fileable_id' => create(User::class)->id,
            'path' => $path,
            'mime' => $mime,
        ]);
        $file->saveQuietly();

        return $file;
    }
}
