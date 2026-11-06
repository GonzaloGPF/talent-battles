<?php

namespace Database\Seeders;

use App\Enums\FileTypes;
use App\Enums\ProductTypes;
use App\Enums\TrackTypes;
use App\Jobs\ProcessAudioJob;
use App\Models\Album;
use App\Models\Design;
use App\Models\File;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use App\Services\FileService;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
         * Clean up all uploaded files
         */
        try {
            Storage::deleteDirectory('files'); // To clean up all files
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        Storage::makeDirectory('files/'.today()->year);
        Storage::makeDirectory(File::PATH_TMP);

        File::flushEventListeners();

        /*
         * User Avatars
         */
        User::whereDoesntHave('files')
            ->get()
            ->each(function (User $user) {
                if (rand(0, 10) > 1) { // 90% of Users will have an Avatar
                    $user->files()->save(File::factory()->avatar()->make());
                }
            });

        /**
         * Album Avatars
         */
        Album::whereDoesntHave('files')
            ->get()
            ->each(function (Album $album) {
                $album->files()->save(File::factory()->image()->make());
            });

        /*
         * Beats
         */
        Track::where('track_type', TrackTypes::BEAT)
            ->whereDoesntHave('files')
            ->get()
            ->each(fn (Track $track) => $this->createTrackFiles($track, File::factory()->beat()->make()));

        /*
         * Samples
         */
        Track::where('track_type', TrackTypes::SAMPLE)
            ->whereDoesntHave('files')
            ->get()
            ->each(fn (Track $track) => $this->createTrackFiles($track, File::factory()->sample()->make()));

        /*
         * Songs
         */
        Track::where('track_type', TrackTypes::SONG)
            ->whereDoesntHave('files')
            ->get()
            ->each(fn (Track $track) => $this->createTrackFiles($track, File::factory()->beat()->make()));

        /*
         * Designs
         */
        Design::whereDoesntHave('files')
            ->get()
            ->each(fn (Design $design) => $design->image()->save(File::factory()->image()->make()));

        /*
         * Products
         */
        Product::whereIn('product_type', [ProductTypes::TRACK, ProductTypes::DESIGN])
            ->with(['orderable.audio', 'orderable.image'])
            ->each(fn (Product $product) => FileService::copyFile($product->orderable->audio ?? $product->orderable->image, [
                'fileable_type' => Product::class,
                'fileable_id' => $product->id,
            ]));
    }

    private function createTrackFiles(Track $track, File $file): void
    {
        $track->files()->save($file);
        $track->update(['name' => $file->name]);

        ProcessAudioJob::dispatch($file);

        if (rand(0, 10) > 1) { // 90% possibility of having an image
            $file = File::factory()->image()->make();
            $file->file_type = FileTypes::IMAGE;
            $track->files()->save($file);
        }
    }
}
