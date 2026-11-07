<?php

namespace Tests\Feature\Pages;

use App\Enums\TrackTypes;
use App\Models\Album;
use App\Models\Design;
use App\Models\File;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SpaPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        File::flushEventListeners();

        // Test requests ask for English by default
        $this->withHeader('Accept-Language', 'es');
    }

    public static function sections(): array
    {
        return [
            'beats' => ['/beats', 'Beats'],
            'songs' => ['/songs', 'Canciones'],
            'samples' => ['/samples', 'Samples'],
            'designs' => ['/designs', 'Diseños'],
            'battles' => ['/battles', 'Batallas'],
            'users' => ['/users', 'Artistas'],
            'contributors' => ['/contributors', 'Contribuidores'],
        ];
    }

    public static function missingModels(): array
    {
        return [
            'track' => ['/beats/999999'],
            'design' => ['/designs/999999'],
            'album' => ['/albums/999999'],
            'user' => ['/users/999999'],
        ];
    }

    public static function privatePages(): array
    {
        return [
            'admin' => ['/admin/users'],
            'profile' => ['/profile'],
            'stats' => ['/stats/balance'],
            'password reset' => ['/password/reset/some-token'],
        ];
    }

    #[Test]
    public function home_renders_its_own_title_description_and_canonical_url()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Talent Battles | La red social de los artistas</title>', false)
            ->assertSee('<meta name="description" content="Sube tus beats, canciones, samples y diseños,', false)
            ->assertSee('<link rel="canonical" href="http://talentbattles.test">', false)
            ->assertDontSee('noindex');
    }

    #[Test]
    #[DataProvider('sections')]
    public function sections_render_their_translated_title(string $url, string $title)
    {
        $this->get($url)
            ->assertOk()
            ->assertSee("<title>$title | Talent Battles</title>", false);
    }

    #[Test]
    public function english_visitors_get_the_meta_tags_in_english()
    {
        $this->withHeader('Accept-Language', 'en')
            ->get('/songs')
            ->assertOk()
            ->assertSee('<title>Songs | Talent Battles</title>', false)
            ->assertSee('<meta property="og:locale" content="en_US">', false);
    }

    #[Test]
    public function track_page_renders_its_name_type_author_and_plain_text_description()
    {
        $track = $this->createTrack(['description' => '<p>Un beat oscuro.</p><p>Para rap &amp; trap.</p>']);

        $this->get("/beats/$track->id")
            ->assertOk()
            ->assertSee('<title>Intro Rap, beat de Zalo Beats | Talent Battles</title>', false)
            ->assertSee('<meta name="description" content="Un beat oscuro. Para rap &amp; trap.">', false)
            ->assertSee("<link rel=\"canonical\" href=\"http://talentbattles.test/beats/$track->id\">", false);
    }

    #[Test]
    public function track_without_description_gets_one_built_from_its_name_type_and_author()
    {
        $track = $this->createTrack(['description' => null]);

        $this->get("/beats/$track->id")
            ->assertOk()
            ->assertSee('<meta name="description" content="Escucha «Intro Rap», beat de Zalo Beats en Talent Battles.">', false);
    }

    #[Test]
    public function long_descriptions_are_cut_to_155_characters()
    {
        $track = $this->createTrack(['description' => str_repeat('a ', 200)]);

        $this->get("/beats/$track->id")
            ->assertOk()
            ->assertSee('<meta name="description" content="'.str_repeat('a ', 77).'a...">', false);
    }

    #[Test]
    public function track_under_the_url_of_another_type_redirects_permanently_to_its_own_url()
    {
        $song = $this->createTrack(['track_type' => TrackTypes::SONG]);

        $this->get("/beats/$song->id")
            ->assertMovedPermanently()
            ->assertRedirect("http://talentbattles.test/songs/$song->id");
    }

    #[Test]
    public function track_image_is_preferred_to_the_album_image()
    {
        $album = create(Album::class);
        $track = $this->createTrack(['album_id' => $album->id]);
        $this->createImage($album);
        $trackImage = $this->createImage($track);

        $this->get("/beats/$track->id")
            ->assertOk()
            ->assertSee("<meta property=\"og:image\" content=\"http://talentbattles.test/files/$trackImage->id\">", false);
    }

    #[Test]
    public function track_without_image_uses_the_album_image_before_the_author_image()
    {
        $album = create(Album::class);
        $track = $this->createTrack(['album_id' => $album->id]);
        $this->createImage($track->createdBy);
        $albumImage = $this->createImage($album);

        $this->get("/beats/$track->id")
            ->assertOk()
            ->assertSee("<meta property=\"og:image\" content=\"http://talentbattles.test/files/$albumImage->id\">", false);
    }

    #[Test]
    public function track_without_image_or_album_uses_the_author_image()
    {
        $track = $this->createTrack();
        $authorImage = $this->createImage($track->createdBy);

        $this->get("/beats/$track->id")
            ->assertOk()
            ->assertSee("<meta property=\"og:image\" content=\"http://talentbattles.test/files/$authorImage->id\">", false);
    }

    #[Test]
    public function page_without_any_image_uses_the_default_image_with_its_size()
    {
        $track = $this->createTrack();

        $this->get("/beats/$track->id")
            ->assertOk()
            ->assertSee('<meta property="og:image" content="http://talentbattles.test/images/logo_big_bg.webp">', false)
            ->assertSee('<meta property="og:image:width" content="2400">', false)
            ->assertSee('<meta property="og:image:height" content="1422">', false);
    }

    #[Test]
    public function design_page_renders_its_name_and_author()
    {
        $author = create(User::class, ['nick' => 'Zalo Beats']);
        $design = create(Design::class, ['name' => 'Logo Azul', 'created_by' => $author->id, 'album_id' => null]);

        $this->get("/designs/$design->id")
            ->assertOk()
            ->assertSee('<title>Logo Azul, diseño de Zalo Beats | Talent Battles</title>', false);
    }

    #[Test]
    public function album_page_renders_its_name_and_author()
    {
        $author = create(User::class, ['nick' => 'Zalo Beats']);
        $album = create(Album::class, ['name' => 'Noches', 'created_by' => $author->id]);

        $this->get("/albums/$album->id")
            ->assertOk()
            ->assertSee('<title>Noches, álbum de Zalo Beats | Talent Battles</title>', false);
    }

    #[Test]
    public function user_page_renders_the_nick_and_bio_as_a_profile()
    {
        $user = create(User::class, ['nick' => 'Zalo Beats', 'bio' => 'Productor de beats']);

        $this->get("/users/$user->id")
            ->assertOk()
            ->assertSee('<title>Zalo Beats | Talent Battles</title>', false)
            ->assertSee('<meta name="description" content="Productor de beats">', false)
            ->assertSee('<meta property="og:type" content="profile">', false);
    }

    #[Test]
    public function user_without_creations_is_not_indexed()
    {
        $user = create(User::class);

        $this->get("/users/$user->id")
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex">', false);
    }

    #[Test]
    public function user_with_creations_is_indexed()
    {
        $track = $this->createTrack();

        $this->get("/users/$track->created_by")
            ->assertOk()
            ->assertDontSee('noindex');
    }

    #[Test]
    #[DataProvider('missingModels')]
    public function missing_models_render_the_spa_with_a_404_and_noindex(string $url)
    {
        $this->get($url)
            ->assertNotFound()
            ->assertSee('id="app"', false)
            ->assertSee('<meta name="robots" content="noindex">', false);
    }

    #[Test]
    #[DataProvider('privatePages')]
    public function private_pages_render_the_spa_without_being_indexed(string $url)
    {
        $this->get($url)
            ->assertOk()
            ->assertSee('id="app"', false)
            ->assertSee('<meta name="robots" content="noindex">', false);
    }

    #[Test]
    public function unknown_paths_are_not_indexed()
    {
        $this->get('/some/frontend/route')
            ->assertNotFound()
            ->assertSee('<meta name="robots" content="noindex">', false);
    }

    #[Test]
    public function json_requests_to_unknown_paths_get_a_json_404()
    {
        $this->getJson('/some/frontend/route')
            ->assertNotFound()
            ->assertJsonPath('data.path', 'http://talentbattles.test/some/frontend/route');
    }

    #[Test]
    public function names_written_by_users_are_escaped_in_the_meta_tags()
    {
        $track = $this->createTrack(['name' => '"><script>alert(1)</script>']);

        // NameFormatter capitalizes each word of the name
        $this->get("/beats/$track->id")
            ->assertOk()
            ->assertDontSee('"><Script>Alert(1)</Script>', false)
            ->assertSee('<meta property="og:title" content="&quot;&gt;&lt;Script&gt;Alert(1)&lt;/Script&gt;, beat de Zalo Beats | Talent Battles">', false);
    }

    /**
     * A beat called "Intro Rap" by "Zalo Beats", without album or images
     */
    private function createTrack(array $attributes = []): Track
    {
        $author = create(User::class, ['nick' => 'Zalo Beats']);

        return create(Track::class, $attributes + [
            'name' => 'Intro Rap',
            'track_type' => TrackTypes::BEAT,
            'created_by' => $author->id,
            'album_id' => null,
        ]);
    }

    private function createImage(Model $owner): File
    {
        return File::factory()->image()->for($owner, 'fileable')->create();
    }
}
