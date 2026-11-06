<?php

namespace Services;

use App\Enums\FileTypes;
use App\Helpers\AudioHelper;
use App\Models\File;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FileServiceTest extends TestCase
{
    use RefreshDatabase;

    private FileService $fileService;

    private User $model;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->signIn();
        $this->fileService = resolve(FileService::class);
        $this->model = create(User::class);
    }

    #[Test]
    public function it_knows_the_directory_of_a_fileable_type()
    {
        $directory = FileService::$FILES_DIRECTORY;
        $year = today()->year;
        $modelName = Str::snake(Str::plural(class_basename($this->model)));

        $this->assertEquals("$directory/$year/$modelName", $this->fileService->getDirectory(get_class($this->model)));
    }

    #[Test]
    public function it_can_save_a_file_from_an_uploaded_file()
    {
        $file = $this->fileService->saveFile($this->model, UploadedFile::fake()->create('Test.txt'));

        $this->assertEquals('Test', $file->name);
        $this->assertEquals('text/plain', $file->mime);
        $this->assertEquals($this->model->id, $file->fileable_id);
    }

    #[Test]
    public function it_can_calculate_hash_name_correctly()
    {
        $file = UploadedFile::fake()->create('Test.txt');

        $hashName = $this->fileService->getHashedFilename($file);

        $this->assertMatchesRegularExpression('/.*.txt$/', $hashName);
    }

    #[Test]
    public function it_can_calculate_hash_name_correctly_based_on_specific_mime()
    {
        $file = UploadedFile::fake()->create('Test.msg', null, 'application/pdf');

        $hashName = $this->fileService->getHashedFilename($file);

        $this->assertMatchesRegularExpression('/.*.pdf$/', $hashName);
    }

    #[Test]
    public function it_can_save_a_file_from_an_uploaded_image()
    {
        $file = $this->fileService->saveFile($this->model, UploadedFile::fake()->image('Test.png'));

        $this->assertEquals('Test', $file->name);
        $this->assertEquals('image/png', $file->mime);
        $this->assertEquals($this->model->id, $file->fileable_id);
    }

    #[Test]
    public function it_can_save_a_file_with_an_specific_file_type()
    {
        $file = $this->fileService->saveFile($this->model, UploadedFile::fake()->image('Test.webp'), FileTypes::IMAGE);

        $this->assertEquals(FileTypes::IMAGE, $file->file_type);
    }

    #[Test]
    public function it_can_update_a_file()
    {
        /** @var File $file */
        $file = create(File::class, ['path' => generateTestFile('original')]);
        $originalPath = $file->path;

        $this->assertTrue(Storage::exists($originalPath));

        $this->model->files()->save($file);

        $this->assertEquals($file->path, $this->model->files->first()->path);

        $this->fileService->updateFile($file, UploadedFile::fake()->create('test.txt'));

        tap($file->fresh(), function (File $file) use ($originalPath) {
            $this->assertFalse(Storage::exists($originalPath));
            $this->assertTrue(Storage::exists($file->path));
            $this->assertEquals($file->path, $this->model->files->first()->fresh()->path);
        });
    }

    #[Test]
    public function when_updating_or_saving_it_will_update_its_file_with_new_one()
    {
        /** @var File $file */
        $file = create(File::class, ['path' => generateTestFile('original')]);
        $originalPath = $file->path;

        $this->assertTrue(Storage::exists($originalPath));

        $this->model->files()->save($file);

        $this->assertEquals($file->path, $this->model->files->first()->path);

        $this->fileService->updateOrSave($this->model, UploadedFile::fake()->create('test.txt'));

        tap($file->fresh(), function (File $file) use ($originalPath) {
            $this->assertFalse(Storage::exists($originalPath));
            $this->assertTrue(Storage::exists($file->path));
            $this->assertEquals($file->path, $this->model->files->first()->fresh()->path);
        });
    }

    #[Test]
    public function when_updating_or_saving_it_will_create_a_new_file_if_model_didnt_have_any_file()
    {
        $this->fileService->updateOrSave($this->model, UploadedFile::fake()->create('test.txt'));

        tap($this->model->fresh()->file, function (File $file) {
            $this->assertTrue(Storage::exists($file->path));
            $this->assertEquals('test', $file->name);
        });
    }

    #[Test]
    public function it_can_get_waveform_from_audio_file()
    {
        $file = make(File::class);

        $this->mock(AudioHelper::class, function (MockInterface $mock) use ($file) {
            $mock->shouldReceive('getWaveformData')
                ->once()
                ->with($file)
                ->andReturn(['waveform' => [1, 2, 3]]);
        });

        $this->fileService = resolve(FileService::class);

        $data = $this->fileService->getWaveform($file);
        $this->assertEquals([1, 2, 3], $data['waveform']);
    }

    #[Test]
    public function it_can_get_duration_from_audio_file()
    {
        $file = make(File::class);

        $this->mock(AudioHelper::class, function (MockInterface $mock) use ($file) {
            $mock->shouldReceive('getSeconds')
                ->once()
                ->with($file)
                ->andReturn(123);
        });

        $this->fileService = resolve(FileService::class);

        $this->assertEquals(123, $this->fileService->getDuration($file));
    }

    #[Test]
    public function it_can_get_bpm_from_audio_file()
    {
        $file = make(File::class);

        $this->mock(AudioHelper::class, function (MockInterface $mock) use ($file) {
            $mock->shouldReceive('getBPM')
                ->once()
                ->with($file)
                ->andReturn(120);
        });

        $this->fileService = resolve(FileService::class);

        $this->assertEquals(120, $this->fileService->getBPM($file));
    }
}
