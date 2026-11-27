<?php

namespace Tests\Feature\Migrations;

use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SetWebpMimeOnConvertedImagesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function images_stored_as_webp_get_the_webp_mime()
    {
        $file = $this->createFile('files/cover.webp', 'image/avif');

        $this->runMigration();

        $this->assertSame('image/webp', $file->fresh()->mime);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function filesThatAreNotConvertedImages(): array
    {
        return [
            'image that was not converted' => ['files/cover.png', 'image/png'],
            'audio' => ['files/beat.mp3', 'audio/mpeg'],
            'non-image with a webp extension' => ['files/data.webp', 'application/octet-stream'],
        ];
    }

    #[Test]
    #[DataProvider('filesThatAreNotConvertedImages')]
    public function files_that_are_not_converted_images_keep_their_mime(string $path, string $mime)
    {
        $file = $this->createFile($path, $mime);

        $this->runMigration();

        $this->assertSame($mime, $file->fresh()->mime);
    }

    private function createFile(string $path, string $mime): File
    {
        /** @var File $file */
        $file = make(File::class, ['path' => $path, 'mime' => $mime]);
        $file->saveQuietly();

        return $file;
    }

    private function runMigration(): void
    {
        (require database_path('migrations/2026_11_27_144520_set_webp_mime_on_converted_images.php'))->up();
    }
}
