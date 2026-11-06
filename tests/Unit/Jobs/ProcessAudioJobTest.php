<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessAudioJob;
use App\Models\File;
use App\Models\Product;
use App\Models\Track;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessAudioJobTest extends TestCase
{
    private File $file;

    private ProcessAudioJob $job;

    private Track $track;

    protected function setUp(): void
    {
        parent::setUp();
        $this->track = makeMock(Track::class, ['bpm' => null]);

        $this->file = makeMock(File::class, [
            'fileable_type' => Track::class,
            'path' => generateTestFile('test', 'mp3'),
            'mime' => 'audio/mpeg',
        ]);
        $this->file->setRelation('fileable', $this->track);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_process_audio_files()
    {
        Storage::shouldReceive('exists')
            ->once()
            ->withArgs([$this->file->path])
            ->andReturn(true);

        $this->track->shouldReceive('forceFill')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('save')
            ->once();

        $this->mock(FileService::class, function (MockInterface $mock) {
            $mock->shouldReceive('isAudio')
                ->once()
                ->withArgs([$this->file])
                ->andReturn(true);

            $mock->shouldReceive('getWaveform')
                ->once()
                ->withArgs([$this->file])
                ->andReturn([
                    'data' => [1, 2, 3],
                ]);

            $mock->shouldReceive('getDuration')
                ->once()
                ->withArgs([$this->file])
                ->andReturn(123);

            $mock->shouldReceive('getBPM')
                ->once()
                ->withArgs([$this->file])
                ->andReturn(120);
        });

        $this->job = resolve(ProcessAudioJob::class, ['file' => $this->file]);
        $this->job->handle(resolve(FileService::class));
    }

    #[Test]
    public function it_wont_update_fileable_if_it_is_not_a_track()
    {
        /** @var $product $product */
        $product = makeMock(Product::class);
        $this->file->fileable_type = Product::class;
        $this->file->setRelation('fileable', $product);

        Storage::shouldReceive('exists')
            ->once()
            ->withArgs([$this->file->path])
            ->andReturn(true);

        $this->mock(FileService::class, function (MockInterface $mock) {
            $mock->shouldReceive('isAudio')
                ->once()
                ->withArgs([$this->file])
                ->andReturn(true);
        });

        $this->job = resolve(ProcessAudioJob::class, ['file' => $this->file]);
        $this->job->handle(resolve(FileService::class));
    }
}
