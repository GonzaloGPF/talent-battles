<?php

namespace Tests\Unit\Helpers;

use App\Helpers\AudioHelper;
use App\Models\File;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The mocks come from tests/mocks/generate-audio.sh: the beat lasts 20 seconds at 120 BPM and the sample 8 seconds at
 * 95 BPM. With 8192 samples per pixel at 44.1 kHz, a waveform is ceil(seconds × 44100 / 8192) pixels long, and holds a
 * minimum and a maximum for each one.
 */
class AudioHelperTest extends TestCase
{
    use RefreshDatabase;

    private Track $track;

    private File $file;

    private AudioHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->helper = resolve(AudioHelper::class);
        $this->track = create(Track::class);
        $this->file = create(File::class, [
            'fileable_id' => $this->track->id,
            'fileable_type' => Track::class,
            'path' => generateTestFile(Str::random(20), 'mp3', Storage::disk('mocks')->get('beats/beat-120-bpm.mp3')),
        ]);
    }

    #[Test]
    public function it_can_process_an_audio_file_correctly()
    {
        $waveformData = $this->helper->getWaveformData($this->file);

        $this->assertEquals(2, $waveformData['version']);
        $this->assertEquals(1, $waveformData['channels']);
        $this->assertEquals(44100, $waveformData['sample_rate']);
        $this->assertEquals(8192, $waveformData['samples_per_pixel']);
        $this->assertEquals(8, $waveformData['bits']);
        $this->assertEquals(108, $waveformData['length']);
        $this->assertCount(216, $waveformData['data']);
    }

    #[Test]
    public function it_can_process_an_audio_file_with_wav_format()
    {
        $this->file->path = generateTestFile(Str::random(20), 'wav', Storage::disk('mocks')->get('samples/sample-095-bpm.wav'));
        $this->file->save();

        $waveformData = $this->helper->getWaveformData($this->file->fresh());
        $this->assertEquals(2, $waveformData['version']);
        $this->assertEquals(1, $waveformData['channels']);
        $this->assertEquals(44100, $waveformData['sample_rate']);
        $this->assertEquals(8192, $waveformData['samples_per_pixel']);
        $this->assertEquals(8, $waveformData['bits']);
        $this->assertEquals(44, $waveformData['length']);
        $this->assertCount(88, $waveformData['data']);
    }

    #[Test]
    public function it_can_process_an_audio_file_correctly_using_a_different_format()
    {
        $waveformData = $this->helper->getWaveformData($this->file, AudioHelper::FORMAT_DATA);

        $this->assertNotNull($waveformData);
    }

    #[Test]
    public function when_processing_audio_it_will_remove_generated_file()
    {
        $this->helper->getWaveformData($this->file);

        $newFilePath = str_replace('.mp3', '.json', $this->file->path);

        Storage::assertMissing($newFilePath);
    }

    #[Test]
    public function it_can_extract_duration_in_seconds_of_a_file()
    {
        $duration = $this->helper->getSeconds($this->file);

        $this->assertEquals(20, $duration);
    }

    #[Test]
    public function it_can_extract_bpm_of_a_file()
    {
        $bpm = $this->helper->getBpm($this->file);

        $this->assertEquals(120, $bpm);
    }

    #[Test]
    public function it_can_extract_bpm_of_a_file_of_wav_format()
    {
        $this->file->path = generateTestFile(Str::random(20), 'wav', Storage::disk('mocks')->get('samples/sample-095-bpm.wav'));
        $this->file->save();

        $bpm = $this->helper->getBpm($this->file);

        $this->assertEquals(95, $bpm);
    }

    #[Test]
    public function it_can_generate_a_watermark()
    {
        $taggedFile = $this->helper->generateWatermark($this->file);

        $filename = pathinfo($this->file->path, PATHINFO_FILENAME);
        $taggedPath = str_replace($filename, "{$filename}_tagged", $this->file->path);

        $this->assertEquals($taggedPath, $taggedFile->path);
        Storage::exists($taggedFile->path);
    }
}
