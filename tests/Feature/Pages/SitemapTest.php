<?php

namespace Tests\Feature\Pages;

use App\Enums\TrackTypes;
use App\Models\Album;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function sitemap_is_served_as_xml()
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    }

    #[Test]
    public function sitemap_lists_the_home_the_sections_and_every_creation_with_its_last_change()
    {
        $this->travelTo('2026-01-02 03:04:05');
        $author = create(User::class);
        $album = create(Album::class, ['created_by' => $author->id]);
        $beat = create(Track::class, ['track_type' => TrackTypes::BEAT, 'created_by' => $author->id, 'album_id' => $album->id]);
        $song = create(Track::class, ['track_type' => TrackTypes::SONG, 'created_by' => $author->id, 'album_id' => $album->id]);
        $design = create(Design::class, ['created_by' => $author->id, 'album_id' => $album->id]);

        $urls = $this->sitemapUrls();

        $this->assertSame([
            'http://talentbattles.test' => null,
            'http://talentbattles.test/beats' => null,
            'http://talentbattles.test/songs' => null,
            'http://talentbattles.test/samples' => null,
            'http://talentbattles.test/designs' => null,
            'http://talentbattles.test/battles' => null,
            'http://talentbattles.test/users' => null,
            'http://talentbattles.test/contributors' => null,
            "http://talentbattles.test/beats/$beat->id" => '2026-01-02T03:04:05+00:00',
            "http://talentbattles.test/songs/$song->id" => '2026-01-02T03:04:05+00:00',
            "http://talentbattles.test/designs/$design->id" => '2026-01-02T03:04:05+00:00',
            "http://talentbattles.test/albums/$album->id" => '2026-01-02T03:04:05+00:00',
            "http://talentbattles.test/users/$author->id" => '2026-01-02T03:04:05+00:00',
        ], $urls);
    }

    #[Test]
    public function users_without_creations_are_left_out_of_the_sitemap()
    {
        $userWithoutCreations = create(User::class);
        $beatMaker = create(User::class);
        $designer = create(User::class);
        create(Track::class, ['created_by' => $beatMaker->id, 'album_id' => null]);
        create(Design::class, ['created_by' => $designer->id, 'album_id' => null]);

        $urls = $this->sitemapUrls();

        $this->assertArrayNotHasKey("http://talentbattles.test/users/$userWithoutCreations->id", $urls);
        $this->assertArrayHasKey("http://talentbattles.test/users/$beatMaker->id", $urls);
        $this->assertArrayHasKey("http://talentbattles.test/users/$designer->id", $urls);
    }

    #[Test]
    public function docs_have_their_own_sitemap_for_robots_txt()
    {
        $this->get('/docs/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>http://talentbattles.test/docs/es/conditions</loc>', false)
            ->assertSee('<loc>http://talentbattles.test/docs/en/conditions</loc>', false);
    }

    /**
     * @return array<string, string|null> each URL of the sitemap, with its last change
     */
    private function sitemapUrls(): array
    {
        $sitemap = simplexml_load_string($this->get('/sitemap.xml')->assertOk()->getContent());

        $urls = [];

        foreach ($sitemap->url as $url) {
            $urls[(string) $url->loc] = isset($url->lastmod) ? (string) $url->lastmod : null;
        }

        return $urls;
    }
}
