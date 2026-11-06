<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessImageJob;
use App\Models\File;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessImageJobTest extends TestCase
{
    private File $file;

    private ProcessImageJob $job;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = makeMock(File::class, [
            'path' => generateTestFile('test', 'png'),
            'mime' => 'image/png',
        ]);
        $this->file->shouldReceive('saveQuietly')
            ->once();
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_process_image_files()
    {
        Storage::shouldReceive('get')
            ->once()
            ->andReturn($this->file->path);

        Storage::shouldReceive('delete')
            ->once();

        Storage::shouldReceive('exists')
            ->once()
            ->withArgs([$this->file->path])
            ->andReturn(true);

        Storage::shouldReceive('put')
            ->once()
            ->withArgs(['SomeFullPath', 'SomeImage'])
            ->andReturn(true);

        Storage::shouldReceive('exists')
            ->once()
            ->withArgs(['SomeFullPath'])
            ->andReturn(true);

        $this->mock(FileService::class, function (MockInterface $mock) {
            $mock->shouldReceive('isImage')
                ->once()
                ->withArgs([$this->file])
                ->andReturn(true);

            $mock->shouldReceive('processImage')
                ->once()
                ->withArgs([$this->file->path, 'webp'])
                ->andReturn('SomeImage');

            $mock->shouldReceive('getFullPath')
                ->once()
                ->withArgs([$this->file->fileable_type, 'SomeImage', 'webp'])
                ->andReturn('SomeFullPath');
        });

        $oldPath = $this->file->path;

        $this->job = resolve(ProcessImageJob::class, ['file' => $this->file]);
        $this->job->handle(resolve(FileService::class));

        $this->assertNotEquals($oldPath, $this->file->path);
    }
}
