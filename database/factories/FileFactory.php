<?php

namespace Database\Factories;

use App\Enums\FileTypes;
use App\Interfaces\HasFilesInterface;
use App\Models\File;
use App\Models\OrderLine;
use App\Models\Product;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    public static array $AVATAR_PATHS = [
        'avatars/avatar-01.webp',
        'avatars/avatar-02.webp',
        'avatars/avatar-03.webp',
        'avatars/avatar-04.webp',
        'avatars/avatar-05.webp',
        'avatars/avatar-06.webp',
    ];

    /**
     * @var array|string[]
     */
    public static array $IMAGE_PATHS = [
        'images/cover-01.webp',
        'images/cover-02.webp',
        'images/cover-03.webp',
        'images/cover-04.webp',
        'images/cover-05.webp',
        'images/cover-06.webp',
        'images/cover-07.webp',
        'images/cover-08.webp',
    ];

    public static array $DESIGN_PATHS = [
        'designs/carpet.webp',
        'designs/mandelbrot.webp',
        'designs/mandelbrot-zoom.webp',
        'designs/rule-30.webp',
        'designs/rule-90.webp',
        'designs/triangle.webp',
    ];

    /**
     * @var array|string[]
     */
    public static array $BEAT_PATHS = [
        'beats/beat-090-bpm.mp3',
        'beats/beat-095-bpm.mp3',
        'beats/beat-100-bpm.mp3',
        'beats/beat-120-bpm.mp3',
        'beats/beat-140-bpm.mp3',
    ];

    public static array $SAMPLE_PATHS = [
        'samples/sample-090-bpm.wav',
        'samples/sample-095-bpm.wav',
        'samples/sample-120-bpm.wav',
    ];

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $fileableModel = loadModels(HasFilesInterface::class)
            ->filter(fn (HasFilesInterface $model) => ! in_array(get_class($model), [OrderLine::class, Product::class]))
            ->random();

        $className = get_class($fileableModel);

        $fileData = $this->getFileData($this->faker->randomElement(self::$IMAGE_PATHS));

        return [
            'file_type' => FileTypes::IMAGE,
            'fileable_type' => $className,
            'fileable_id' => associateTo($className),
            'path' => $fileData['path'],
            'name' => $fileData['name'],
            'mime' => $fileData['mime'],
            'secret' => uniqid(),
        ];
    }

    public function image(): FileFactory
    {
        $fileData = $this->getFileData($this->faker->randomElement(self::$IMAGE_PATHS));

        return $this->state([
            'file_type' => FileTypes::IMAGE,
            'path' => $fileData['path'],
            'name' => $fileData['name'],
            'mime' => $fileData['mime'],
        ]);
    }

    public function design(): FileFactory
    {
        $fileData = $this->getFileData($this->faker->randomElement(self::$DESIGN_PATHS));

        return $this->state([
            'file_type' => FileTypes::IMAGE,
            'path' => $fileData['path'],
            'name' => $fileData['name'],
            'mime' => $fileData['mime'],
        ]);
    }

    public function avatar(): FileFactory
    {
        $fileData = $this->getFileData($this->faker->randomElement(self::$AVATAR_PATHS));

        return $this->state([
            'file_type' => FileTypes::IMAGE,
            'path' => $fileData['path'],
            'name' => $fileData['name'],
            'mime' => $fileData['mime'],
        ]);
    }

    public function beat(): FileFactory
    {
        $fileData = $this->getFileData($this->faker->randomElement(self::$BEAT_PATHS));

        return $this->state([
            'file_type' => FileTypes::AUDIO,
            'path' => $fileData['path'],
            'name' => $fileData['name'],
            'mime' => $fileData['mime'],
        ]);
    }

    public function song(): FileFactory
    {
        $fileData = $this->getFileData($this->faker->randomElement(self::$BEAT_PATHS));

        return $this->state([
            'file_type' => FileTypes::AUDIO,
            'path' => $fileData['path'],
            'name' => $fileData['name'],
            'mime' => $fileData['mime'],
        ]);
    }

    public function sample(): FileFactory
    {
        $fileData = $this->getFileData($this->faker->randomElement(self::$SAMPLE_PATHS));

        return $this->state([
            'file_type' => FileTypes::AUDIO,
            'path' => $fileData['path'],
            'name' => $fileData['name'],
            'mime' => $fileData['mime'],
        ]);
    }

    private function getFileData(?string $filePath = null): array
    {
        if (app()->environment('testing')) {
            return $this->fileData();
        }

        return $this->fileData($filePath);
    }

    private function fileData(?string $path = null): array
    {
        if (! $path) {
            $name = Str::snake($this->faker->words(rand(1, 3), true));
            $mime = 'test';
            $path = 'path/to/file.txt';
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $filename = md5(uniqid());

            $targetPath = "files/$filename.$extension";

            $content = Storage::disk('mocks')->get($path);

            Storage::put($targetPath, $content);

            $name = pathinfo($path, PATHINFO_FILENAME);
            $mime = array_flip(FileService::$MIME_EXT)[$extension];
            $path = $targetPath;
        }

        return compact('path', 'mime', 'name');
    }
}
