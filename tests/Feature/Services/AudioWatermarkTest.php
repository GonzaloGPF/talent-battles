<?php

namespace Tests\Feature\Services;

use App\Facades\Logger;
use App\Objects\FFMpegManager;
use App\Services\FileService;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Tests\TestCase;

/**
 * Uses the real ffmpeg binary: the jobs that depend on it mock FileService.
 */
class AudioWatermarkTest extends TestCase
{
    #[Test]
    public function it_can_add_a_watermark_to_an_audio_file()
    {
        $audio = file_get_contents(base_path('tests/mocks/beats/beat-120-bpm.mp3'));

        Storage::fake('public');
        Storage::disk('public')->put(FileService::WATERMARK_AUDIO, $audio);
        Storage::put('tracks/abc123.mp3', $audio);

        $path = resolve(FFMpegManager::class)->watermarkAudio('tracks/abc123.mp3');

        $this->assertSame('tracks/abc123_tagged.mp3', $path);
        Storage::assertExists($path);
        $this->assertGreaterThan(0, FFMpeg::open($path)->getDurationInSeconds());
    }

    #[Test]
    public function a_watermark_that_fails_is_logged_and_gives_no_file()
    {
        $audio = file_get_contents(base_path('tests/mocks/beats/beat-120-bpm.mp3'));

        Storage::fake();
        Storage::fake('public');
        Storage::disk('public')->put(FileService::WATERMARK_AUDIO, $audio);
        Storage::put('tracks/abc123.mp3', $audio);
        Storage::makeDirectory('tracks/abc123_tagged.mp3');
        Logger::shouldReceive('exception')->once();

        $this->assertNull(resolve(FFMpegManager::class)->watermarkAudio('tracks/abc123.mp3'));
    }
}
