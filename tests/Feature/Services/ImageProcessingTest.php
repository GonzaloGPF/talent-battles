<?php

namespace Tests\Feature\Services;

use App\Models\Design;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Uses the real image driver: jobs that depend on it mock FileService.
 */
class ImageProcessingTest extends TestCase
{
    use RefreshDatabase;

    private FileService $fileService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileService = resolve(FileService::class);
    }

    private function image(int $width, int $height): string
    {
        return UploadedFile::fake()->image('image.png', $width, $height)->getContent();
    }

    #[Test]
    public function it_scales_down_big_images_keeping_the_aspect_ratio()
    {
        $processed = $this->fileService->processImage($this->image(1600, 1200), 'webp');

        $size = getimagesizefromstring($processed);

        $this->assertSame(FileService::$IMG_WITH, $size[0]);
        $this->assertSame(600, $size[1]);
        $this->assertSame('image/webp', $size['mime']);
    }

    #[Test]
    public function it_does_not_scale_up_small_images()
    {
        $processed = $this->fileService->processImage($this->image(400, 300), 'webp');

        [$width, $height] = getimagesizefromstring($processed);

        $this->assertSame(400, $width);
        $this->assertSame(300, $height);
    }

    #[Test]
    public function it_can_add_a_watermark_to_a_design()
    {
        Storage::fake('public');
        Storage::disk('public')->put(FileService::WATERMARK_IMAGE, $this->image(50, 50));
        Storage::put('designs/abc123.png', $this->image(200, 200));

        $design = create(Design::class);
        $file = create(File::class, ['path' => 'designs/abc123.png']);

        $this->fileService->watermarkDesign($design, $file);

        Storage::assertExists('designs/abc123_tagged.png');
        $this->assertSame([200, 200], array_slice(getimagesizefromstring(Storage::get('designs/abc123_tagged.png')), 0, 2));
    }
}
