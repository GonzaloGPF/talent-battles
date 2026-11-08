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

    /**
     * A JPEG of 1200×630: the format and size every social network shows when a link is shared.
     */
    private const DEFAULT_IMAGE = 'images/share.jpg';

    private const DEFAULT_IMAGE_TYPE = 'image/jpeg';

    private const DEFAULT_IMAGE_WIDTH = 1200;

    private const DEFAULT_IMAGE_HEIGHT = 630;

    private const LOGO = 'images/logo_black_on_white.webp';

    public function home(): PageMeta
    {
        $home = route('welcome');

        return $this->page(
            title: trans('seo.home_title', ['app' => config('app.name')]),
            description: trans('seo.description'),
            url: $home,
            structuredData: [
                [
                    '@type' => 'WebSite',
                    '@id' => "$home#website",
                    'url' => $home,
                    'name' => config('app.name'),
                    'publisher' => ['@id' => "$home#organization"],
                ],
                [
                    '@type' => 'Organization',
                    '@id' => "$home#organization",
                    'url' => $home,
                    'name' => config('company.name'),
                    'logo' => asset(self::LOGO),
                ],
            ],
        );
    }

    public function section(string $section): PageMeta
    {
        $name = trans("seo.pages.$section.title");
        $url = route('pages.index', ['section' => $section]);

        return $this->page(
            title: $this->title($name),
            description: trans("seo.pages.$section.description"),
            url: $url,
            structuredData: [$this->breadcrumbs([[$name, $url]])],
        );
    }

    public function track(Track $track): PageMeta
    {
        $track->loadMissing(['image', 'album.image', 'createdBy.image']);

        $replace = $this->creationReplacements($track->name, $track->createdBy) + [
            'type' => trans("seo.track_types.{$track->track_type->value}"),
        ];
        $section = $track->track_type->getUrlSegment();
        $url = $this->trackUrl($track);

        return $this->page(
            title: $this->title(trans('seo.track_title', $replace)),
            description: $this->description($track->description, trans('seo.track_description', $replace)),
            url: $url,
            image: $track->image ?? $track->album->image ?? $track->createdBy?->image,
            structuredData: [$this->breadcrumbs([
                [trans("seo.pages.$section.title"), route('pages.index', ['section' => $section])],
                [$track->name, $url],
            ])],
        );
    }

    public function design(Design $design): PageMeta
    {
        $design->loadMissing(['image', 'album.image', 'createdBy.image']);

        $replace = $this->creationReplacements($design->name, $design->createdBy);
        $url = route('pages.designs.show', $design);

        return $this->page(
            title: $this->title(trans('seo.design_title', $replace)),
            description: $this->description($design->description, trans('seo.design_description', $replace)),
            url: $url,
            image: $design->image ?? $design->album->image ?? $design->createdBy?->image,
            structuredData: [$this->breadcrumbs([
                [trans('seo.pages.designs.title'), route('pages.index', ['section' => 'designs'])],
                [$design->name, $url],
            ])],
        );
    }

    /**
     * There is no list of albums, so the breadcrumbs of an album go through its author.
     */
    public function album(Album $album): PageMeta
    {
        $album->loadMissing(['image', 'createdBy.image']);

        $replace = $this->creationReplacements($album->name, $album->createdBy);
        $url = route('pages.albums.show', $album);
        $author = $album->createdBy ? [[$album->createdBy->getNickName(), route('pages.users.show', $album->createdBy)]] : [];

        return $this->page(
            title: $this->title(trans('seo.album_title', $replace)),
            description: $this->description($album->description, trans('seo.album_description', $replace)),
            url: $url,
            image: $album->image ?? $album->createdBy?->image,
            structuredData: [$this->breadcrumbs([
                [trans('seo.pages.users.title'), route('pages.index', ['section' => 'users'])],
                ...$author,
                [$album->name, $url],
            ])],
        );
    }

    /**
     * Profiles without creations are thin content, so search engines are asked not to index them.
     */
    public function user(User $user): PageMeta
    {
        $user->loadMissing('image');

        $name = $user->getNickName();
        $url = route('pages.users.show', $user);
        $description = $this->description(
            $user->bio ?: $user->quote,
            trans('seo.user_description', ['name' => $name, 'app' => config('app.name')])
        );
        $image = $this->imageUrl($user->image);

        return $this->page(
            title: $this->title($name),
            description: $description,
            url: $url,
            image: $user->image,
            type: 'profile',
            indexable: $user->tracks()->exists() || $user->designs()->exists(),
            structuredData: [
                [
                    '@type' => 'ProfilePage',
                    'url' => $url,
                    'dateCreated' => $user->created_at?->toAtomString(),
                    'dateModified' => $user->updated_at?->toAtomString(),
                    'mainEntity' => [
                        '@type' => 'Person',
                        'identifier' => (string) $user->id,
                        'name' => $name,
                        'description' => $description,
                        'image' => $image,
                    ],
                ],
                $this->breadcrumbs([
                    [trans('seo.pages.users.title'), route('pages.index', ['section' => 'users'])],
                    [$name, $url],
                ]),
            ],
        );
    }

    public function trackUrl(Track $track): string
    {
        return route('pages.tracks.show', [
            'trackType' => $track->track_type->getUrlSegment(),
            'track' => $track,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $structuredData
     */
    private function page(
        string $title,
        string $description,
        string $url,
        ?File $image = null,
        string $type = 'website',
        bool $indexable = true,
        array $structuredData = [],
    ): PageMeta {
        return new PageMeta(
            title: $title,
            description: $description,
            url: $url,
            image: $this->imageUrl($image),
            imageType: $image ? $image->mime : self::DEFAULT_IMAGE_TYPE,
            locale: app()->getLocale() === 'es' ? 'es_ES' : 'en_US',
            type: $type,
            indexable: $indexable,
            imageWidth: $image ? null : self::DEFAULT_IMAGE_WIDTH,
            imageHeight: $image ? null : self::DEFAULT_IMAGE_HEIGHT,
            structuredData: $structuredData,
        );
    }

    private function imageUrl(?File $image): string
    {
        return $image ? route('web.files.show', $image) : asset(self::DEFAULT_IMAGE);
    }

    private function title(string $page): string
    {
        return trans('seo.title', ['page' => $page, 'app' => config('app.name')]);
    }

    /**
     * Every trail starts at the home page. Pages are pairs and not name => URL, because two pages
     * can have the same name, and a name like "2025" would become an integer key.
     *
     * @param  array<int, array{string, string}>  $pages  name and URL of each page after the home page
     * @return array<string, mixed>
     */
    private function breadcrumbs(array $pages): array
    {
        $trail = [[config('app.name'), route('welcome')], ...$pages];

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $page, int $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $page[0],
                'item' => $page[1],
            ], $trail, array_keys($trail)),
        ];
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
