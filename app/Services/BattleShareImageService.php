<?php

namespace App\Services;

use App\Facades\Logger;
use App\Models\Battle;
use App\Models\Design;
use App\Models\File;
use App\Models\Track;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;

/**
 * The image shared with a link to a battle: its two creations face to face, at the 1200×630 of every link preview.
 * It is drawn once for each version of what it shows and kept on the default disk. The version goes in its URL, so
 * social networks fetch it again when a creation changes.
 */
class BattleShareImageService
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    private const DIRECTORY = 'battles/share';

    private const COVER_SIZE = 400;

    private const COVER_TOP = 95;

    /**
     * Left edge of each cover: the left one and the right one, with room for "VS" between them.
     */
    private const COVER_LEFTS = [80, 720];

    private const BACKGROUND = '#121212';

    private const EMPTY_COVER = '#1E1E1E';

    private const ACCENT = '#787AF6';

    public function __construct(private readonly ImageManager $imageManager) {}

    public function url(Battle $battle): string
    {
        return route('battles.share_image', ['battle' => $battle, 'v' => $this->version($battle)]);
    }

    /**
     * Changes whenever something the image shows changes: a creation, its name, its author or its cover. A cover is
     * replaced in place, keeping its id, so its path is what tells it has changed.
     */
    public function version(Battle $battle): string
    {
        $shown = $this->creations($battle)->map(fn (Track|Design $creation) => [
            $creation->id,
            $creation->name,
            $creation->color,
            $creation->createdBy?->getNickName(),
            $this->cover($creation)?->path,
        ]);

        return substr(md5((string) json_encode($shown)), 0, 12);
    }

    /**
     * The path of the image on the default disk, drawn first if this version is not there yet. Older versions of the
     * image of the battle are deleted.
     */
    public function path(Battle $battle): string
    {
        $path = self::DIRECTORY."/{$battle->id}-{$this->version($battle)}.jpg";

        if (! Storage::exists($path)) {
            Storage::delete(collect(Storage::files(self::DIRECTORY))
                ->filter(fn (string $file) => Str::startsWith(basename($file), "{$battle->id}-"))
                ->all());

            Storage::put($path, $this->draw($battle));
        }

        return $path;
    }

    private function draw(Battle $battle): string
    {
        $creations = $this->creations($battle);
        $image = $this->imageManager->createImage(self::WIDTH, self::HEIGHT)->fill(self::BACKGROUND);

        $image->text(Str::upper((string) config('app.name')), self::WIDTH / 2, 50, fn (FontFactory $font) => $font
            ->filename(resource_path('fonts/belmont_regular.otf'))
            ->size(34)
            ->color(self::ACCENT)
            ->align('center', 'center'));

        foreach (self::COVER_LEFTS as $index => $left) {
            $this->drawCreation($image, $creations->get($index), $left);
        }

        $image->text('VS', self::WIDTH / 2, self::COVER_TOP + self::COVER_SIZE / 2, fn (FontFactory $font) => $font
            ->filename(resource_path('fonts/belmont_regular.otf'))
            ->size(120)
            ->color('#FFFFFF')
            ->align('center', 'center'));

        return $image->encodeUsingMediaType('image/jpeg', quality: 85)->toString();
    }

    /**
     * The cover of the creation with its name and author below. A battle still waiting for a rival shows a question
     * mark in the empty place.
     */
    private function drawCreation(ImageInterface $image, Track|Design|null $creation, int $left): void
    {
        $center = $left + self::COVER_SIZE / 2;

        if (! $creation) {
            $image->drawRectangle(fn (RectangleFactory $rectangle) => $rectangle
                ->at($left, self::COVER_TOP)
                ->size(self::COVER_SIZE, self::COVER_SIZE)
                ->background(self::EMPTY_COVER));
            $this->write($image, '?', $center, self::COVER_TOP + self::COVER_SIZE / 2, 160, '#555555', 'center');

            return;
        }

        $cover = $this->coverImage($creation);

        if ($cover) {
            $image->insert($cover, $left, self::COVER_TOP);
        } else {
            $image->drawRectangle(fn (RectangleFactory $rectangle) => $rectangle
                ->at($left, self::COVER_TOP)
                ->size(self::COVER_SIZE, self::COVER_SIZE)
                ->background($creation->color ?: self::ACCENT));
        }

        $this->write($image, Str::limit($creation->name, 22), $center, 525, 30, '#FFFFFF');
        $this->write($image, Str::limit((string) $creation->createdBy?->getNickName(), 30), $center, 572, 22, '#9E9E9E');
    }

    private function write(ImageInterface $image, string $text, int $x, int $y, int $size, string $color, string $vertical = 'top'): void
    {
        $image->text($text, $x, $y, fn (FontFactory $font) => $font
            ->filename(resource_path('fonts/dejavu_sans_bold.ttf'))
            ->size($size)
            ->color($color)
            ->align('center', $vertical));
    }

    /**
     * The cover cropped to a square. A cover that cannot be read is left out, so the battle can still be shared.
     */
    private function coverImage(Track|Design $creation): ?ImageInterface
    {
        $file = $this->cover($creation);

        if (! $file || ! Storage::exists($file->path)) {
            return null;
        }

        try {
            return $this->imageManager
                ->decodeBinary((string) Storage::get($file->path))
                ->cover(self::COVER_SIZE, self::COVER_SIZE);
        } catch (Exception $exception) {
            Logger::exception($exception, ['file' => $file->id]);

            return null;
        }
    }

    /**
     * The same image the page of the creation shares: its own, its album's or its author's.
     */
    private function cover(Track|Design $creation): ?File
    {
        return $creation->image ?? $creation->album->image ?? $creation->createdBy?->image;
    }

    /**
     * @return Collection<int, Track>|Collection<int, Design>
     */
    private function creations(Battle $battle): Collection
    {
        $battle->loadMissing([
            'tracks.image', 'tracks.album.image', 'tracks.createdBy.image',
            'designs.image', 'designs.album.image', 'designs.createdBy.image',
        ]);

        return $battle->getCreations()->values()->toBase();
    }
}
