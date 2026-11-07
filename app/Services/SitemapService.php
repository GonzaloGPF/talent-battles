<?php

namespace App\Services;

use App\Http\Controllers\PageController;
use App\Models\Album;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;

class SitemapService
{
    public function __construct(private PageMetaService $pageMetaService) {}

    /**
     * The pages that search engines may index, so users without creations are left out. The
     * pages of the docs are in the sitemap of LaRecipe, /docs/sitemap.xml.
     *
     * @return LazyCollection<int, array{url: string, updated_at: DateTimeInterface|null}>
     */
    public function entries(): LazyCollection
    {
        return LazyCollection::make(function () {
            yield $this->entry(route('welcome'));

            foreach (PageController::SECTIONS as $section) {
                yield $this->entry(route('pages.index', ['section' => $section]));
            }

            foreach (Track::query()->select(['id', 'track_type', 'updated_at'])->lazyById() as $track) {
                yield $this->entry($this->pageMetaService->trackUrl($track), $track->updated_at);
            }

            foreach (Design::query()->select(['id', 'updated_at'])->lazyById() as $design) {
                yield $this->entry(route('pages.designs.show', $design), $design->updated_at);
            }

            foreach (Album::query()->select(['id', 'updated_at'])->lazyById() as $album) {
                yield $this->entry(route('pages.albums.show', $album), $album->updated_at);
            }

            $creators = User::query()
                ->select(['id', 'updated_at'])
                ->where(fn (Builder $query) => $query->has('tracks')->orHas('designs'));

            foreach ($creators->lazyById() as $creator) {
                yield $this->entry(route('pages.users.show', $creator), $creator->updated_at);
            }
        });
    }

    /**
     * @return array{url: string, updated_at: DateTimeInterface|null}
     */
    private function entry(string $url, ?DateTimeInterface $updatedAt = null): array
    {
        return ['url' => $url, 'updated_at' => $updatedAt];
    }
}
