<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateWatermark;
use App\Models\Design;
use App\Models\File;
use App\Models\Track;
use App\Services\FileService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateWatermarkTest extends TestCase
{
    use RefreshDatabase;

    private Track $track;

    private Design $design;

    private CreateWatermark $job;

    private File $fileTrack;

    private File $fileDesign;

    protected function setUp(): void
    {
        parent::setUp();
        $this->track = makeMock(Track::class);
        $this->design = makeMock(Design::class);

        $this->fileTrack = makeMock(File::class, [
            'fileable_type' => Track::class,
            'path' => generateTestFile('test', 'mp3'),
            'mime' => 'audio/mpeg',
        ]);
        $this->fileTrack->setRelation('fileable', $this->track);

        $this->fileDesign = makeMock(File::class, [
            'fileable_type' => Design::class,
            'path' => generateTestFile('test', 'jpg'),
            'mime' => 'image/jpeg',
        ]);
        $this->fileDesign->setRelation('fileable', $this->design);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_can_create_a_watermark_for_a_track()
    {
        $this->mock(FileService::class, function (MockInterface $mock) {
            $mock->shouldReceive('watermarkTrack')
                ->once()
                ->with($this->track, $this->fileTrack)
                ->andReturn();
        });

        $this->job = resolve(CreateWatermark::class, ['creation' => $this->track, 'file' => $this->fileTrack]);
        $this->job->handle(resolve(FileService::class));
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_can_create_a_watermark_for_a_design()
    {
        $this->mock(FileService::class, function (MockInterface $mock) {
            $mock->shouldReceive('watermarkDesign')
                ->once()
                ->with($this->design, $this->fileTrack)
                ->andReturn();
        });

        $this->job = resolve(CreateWatermark::class, ['creation' => $this->design, 'file' => $this->fileTrack]);
        $this->job->handle(resolve(FileService::class));
    }
}
