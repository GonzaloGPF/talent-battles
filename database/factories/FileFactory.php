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
        'beats/Alright.mp3',
        'beats/AZ-Style.mp3',
        'beats/Boom.mp3',
        'beats/Coldness.mp3',
        'beats/DrubZent.mp3',
        'beats/Hard Beat.mp3',
        'beats/Horrorcore.mp3',
        'beats/Muy tranquilo.mp3',
        'beats/Nitruz 1.mp3',
        'beats/Shine.mp3',
        'beats/Snow Gun.mp3',
        'beats/Strings Beat.mp3',
        'beats/Track Banger.mp3',
        'beats/Violin.mp3',
        'beats/Welcome to Hate.mp3',
        'beats/13 asesinos.mp3',
        'beats/Frenzy.mp3',
        'beats/Omnipresente.mp3',
        'beats/Victoria.mp3',
        'beats/Unsolved Mysteries.mp3',
        'beats/Gunitdown 2.mp3',
        'beats/Alliance.mp3',
    ];

    public static array $SAMPLE_PATHS = [
        'samples/bass-hipless-2.wav',
        'samples/bass-hipless-3.wav',
        'samples/decessus-smokey-bass.wav',
        'samples/ethnic-vibes.wav',
        'samples/flute-jam.wav',
        'samples/groove.wav',
        'samples/guitar-clip.wav',
        'samples/lil-peep-guitar.wav',
        'samples/my-way.wav',
        'samples/scratches_1.wav',
        'samples/scratches_2.wav',
        'samples/x-russ-guitar.wav',
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
