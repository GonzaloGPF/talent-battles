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
        'avatars/avatar_1.webp',
        'avatars/avatar_2.webp',
        'avatars/avatar_3.webp',
        'avatars/avatar_4.webp',
        'avatars/avatar_5.webp',
        'avatars/avatar_6.webp',
    ];

    /**
     * @var array|string[]
     */
    public static array $IMAGE_PATHS = [
        'images/adventure.webp',
        'images/artist.webp',
        'images/artistic.webp',
        'images/dj.webp',
        'images/electronic.webp',
        'images/forest.webp',
        'images/graffiti.webp',
        'images/hiphop.webp',
        'images/music.webp',
        'images/waterfall.webp',
        'images/chubby.webp',
        'images/complements.webp',
        'images/fashion.webp',
        'images/niggas.webp',
        'images/niggas_2.webp',
        'images/radio.webp',
        'images/radio_2.webp',
        'images/urban.webp',
    ];

    public static array $DESIGN_PATHS = [
        'designs/dog_draw.webp',
        'designs/full_design.webp',
        'designs/jack_sparrow.webp',
        'designs/lion.webp',
        'designs/portrait.webp',
        'designs/realistic_dog.webp',
        'designs/robot_wolf.webp',
        'designs/street_draw.webp',
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
