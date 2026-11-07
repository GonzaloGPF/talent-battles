<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Design;
use App\Models\File;
use App\Models\Track;
use App\Models\User;
use App\Objects\PageMeta;
use Illuminate\Support\Str;

class PageMetaService
{
    private const DESCRIPTION_LENGTH = 155;

    private const DEFAULT_IMAGE = 'images/logo_big_bg.webp';

    private const DEFAULT_IMAGE_WIDTH = 2400;

    private const DEFAULT_IMAGE_HEIGHT = 1422;

    public function home(): PageMeta
    {
        return $this->page(
            title: trans('seo.home_title', ['app' => config('app.name')]),
            description: trans('seo.description'),
            url: route('welcome'),
        );
    }

    public function section(string $section): PageMeta
    {
        return $this->page(
            title: $this->title(trans("seo.pages.$section.title")),
            description: trans("seo.pages.$section.description"),
            url: route('pages.index', ['section' => $section]),
        );
    }

    public function track(Track $track): PageMeta
    {
        $track->loadMissing(['image', 'album.image', 'createdBy.image']);

        $replace = $this->creationReplacements($track->name, $track->createdBy) + [
            'type' => trans("seo.track_types.{$track->track_type->value}"),
        ];

        return $this->page(
            title: $this->title(trans('seo.track_title', $replace)),
            description: $this->description($track->description, trans('seo.track_description', $replace)),
            url: $this->trackUrl($track),
            image: $track->image ?? $track->album->image ?? $track->createdBy?->image,
        );
    }

    public function design(Design $design): PageMeta
    {
        $design->loadMissing(['image', 'album.image', 'createdBy.image']);

        $replace = $this->creationReplacements($design->name, $design->createdBy);

        return $this->page(
            title: $this->title(trans('seo.design_title', $replace)),
            description: $this->description($design->description, trans('seo.design_description', $replace)),
            url: route('pages.designs.show', $design),
            image: $design->image ?? $design->album->image ?? $design->createdBy?->image,
        );
    }

    public function album(Album $album): PageMeta
    {
        $album->loadMissing(['image', 'createdBy.image']);

        $replace = $this->creationReplacements($album->name, $album->createdBy);

        return $this->page(
            title: $this->title(trans('seo.album_title', $replace)),
            description: $this->description($album->description, trans('seo.album_description', $replace)),
            url: route('pages.albums.show', $album),
            image: $album->image ?? $album->createdBy?->image,
        );
    }

    /**
     * Profiles without creations are thin content, so search engines are asked not to index them.
     */
    public function user(User $user): PageMeta
    {
        $user->loadMissing('image');

        $name = $user->getNickName();

        return $this->page(
            title: $this->title($name),
            description: $this->description(
                $user->bio ?: $user->quote,
                trans('seo.user_description', ['name' => $name, 'app' => config('app.name')])
            ),
            url: route('pages.users.show', $user),
            image: $user->image,
            type: 'profile',
            indexable: $user->tracks()->exists() || $user->designs()->exists(),
        );
    }

    public function trackUrl(Track $track): string
    {
        return route('pages.tracks.show', [
            'trackType' => $track->track_type->getUrlSegment(),
            'track' => $track,
        ]);
    }

    private function page(
        string $title,
        string $description,
        string $url,
        ?File $image = null,
        string $type = 'website',
        bool $indexable = true,
    ): PageMeta {
        return new PageMeta(
            title: $title,
            description: $description,
            url: $url,
            image: $image ? route('web.files.show', $image) : asset(self::DEFAULT_IMAGE),
            imageType: $image ? $image->mime : 'image/webp',
            locale: app()->getLocale() === 'es' ? 'es_ES' : 'en_US',
            type: $type,
            indexable: $indexable,
            imageWidth: $image ? null : self::DEFAULT_IMAGE_WIDTH,
            imageHeight: $image ? null : self::DEFAULT_IMAGE_HEIGHT,
        );
    }

    private function title(string $page): string
    {
        return trans('seo.title', ['page' => $page, 'app' => config('app.name')]);
    }

    /**
     * @return array{name: string, author: string, app: string}
     */
    private function creationReplacements(string $name, ?User $author): array
    {
        return [
            'name' => $name,
            'author' => $author?->getNickName() ?? trans('seo.unknown_author'),
            'app' => config('app.name'),
        ];
    }

    /**
     * Descriptions come from a rich text editor. A space goes before each tag so that paragraphs
     * do not run into each other once the tags are removed.
     */
    private function description(?string $text, string $fallback): string
    {
        $plainText = Str::of(html_entity_decode(strip_tags(str_replace('<', ' <', (string) $text)), ENT_QUOTES | ENT_HTML5))
            ->squish();

        return $plainText->isEmpty()
            ? $fallback
            : $plainText->limit(self::DESCRIPTION_LENGTH)->toString();
    }
}
