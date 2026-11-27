<?php

namespace Tests\Feature\Battles;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\File;
use App\Models\Track;
use App\Models\User;
use App\Services\BattleShareImageService;
use App\Services\FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BattleShareImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The middle of the left cover in the image.
     */
    private const LEFT_COVER_CENTER = [280, 295];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    #[Test]
    public function a_battle_is_shared_with_a_jpeg_of_its_two_creations_cached_for_good()
    {
        $battle = $this->battle();

        $response = $this->get($this->service()->url($battle))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');

        $this->assertStringContainsString('immutable', $response->headers->get('Cache-Control'));

        $image = $this->decode($response->getContent());
        $this->assertSame(BattleShareImageService::WIDTH, $image->width());
        $this->assertSame(BattleShareImageService::HEIGHT, $image->height());
    }

    #[Test]
    public function the_image_url_has_no_extension_so_nginx_hands_it_to_laravel()
    {
        $path = (string) parse_url($this->service()->url($this->battle()), PHP_URL_PATH);

        $this->assertSame('', pathinfo($path, PATHINFO_EXTENSION));
    }

    #[Test]
    public function an_old_version_gets_the_current_image_cached_for_an_hour()
    {
        $battle = $this->battle();

        $this->get(route('battles.share_image', ['battle' => $battle, 'v' => 'old']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=3600, public');
    }

    #[Test]
    public function the_image_is_drawn_once_and_drawn_again_when_a_creation_changes()
    {
        $battle = $this->battle();

        $first = $this->service()->path($battle);
        $this->assertSame($first, $this->service()->path($battle->fresh()));

        $battle->tracks->first()->update(['name' => 'Otro nombre']);
        $second = $this->service()->path($battle->fresh());

        $this->assertNotSame($first, $second);
        $this->assertSame([$second], Storage::files('battles/share'));
    }

    #[Test]
    public function a_creation_is_shown_with_its_cover()
    {
        $battle = $this->battle();
        Storage::put('images/cover.jpg', app(ImageManager::class)->createImage(50, 50)->fill('#FF0000')->encodeUsingMediaType('image/jpeg')->toString());
        File::factory()->image()->for($battle->tracks->first(), 'fileable')->create(['path' => 'images/cover.jpg', 'mime' => 'image/jpeg']);

        $this->assertLeftCoverIs([255, 0, 0], $battle->fresh());
    }

    #[Test]
    public function a_replaced_cover_is_drawn_in_place_of_the_old_one()
    {
        $battle = $this->battle();
        Storage::put('images/cover.jpg', $this->jpeg('#FF0000'));
        $cover = File::factory()->image()->for($battle->tracks->first(), 'fileable')->create(['path' => 'images/cover.jpg', 'mime' => 'image/jpeg']);
        $this->service()->path($battle->fresh());

        app(FileService::class)->updateFile($cover, UploadedFile::fake()->createWithContent('cover.jpg', $this->jpeg('#0000FF')));

        $this->assertLeftCoverIs([0, 0, 255], $battle->fresh());
    }

    #[Test]
    public function a_creation_without_a_cover_that_can_be_read_is_shown_with_its_color()
    {
        $battle = $this->battle();
        Storage::put('images/broken.jpg', 'not an image');
        File::factory()->image()->for($battle->tracks->first(), 'fileable')->create(['path' => 'images/broken.jpg', 'mime' => 'image/jpeg']);

        $this->assertLeftCoverIs([0, 255, 0], $battle->fresh());
    }

    #[Test]
    public function a_battle_waiting_for_a_rival_is_shared_too()
    {
        $battle = $this->battle(withOpponent: false);

        $this->get($this->service()->url($battle))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    #[Test]
    public function the_battle_page_shares_its_image()
    {
        $battle = $this->battle();
        $url = e($this->service()->url($battle));

        $this->get(route('pages.battles.show', $battle))
            ->assertOk()
            ->assertSee("<meta property=\"og:image\" content=\"$url\">", false)
            ->assertSee('<meta property="og:image:type" content="image/jpeg">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false);
    }

    private function battle(bool $withOpponent = true): Battle
    {
        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS->value, 'battle_type' => BattleTypes::TRACK]);
        $tracks = [create(Track::class, [
            'name' => 'Intro Rap',
            'color' => '#00FF00',
            'album_id' => null,
            'created_by' => create(User::class, ['nick' => 'Zalo Beats'])->id,
        ])->id];

        if ($withOpponent) {
            $tracks[] = create(Track::class, [
                'name' => 'Noche',
                'album_id' => null,
                'created_by' => create(User::class, ['nick' => 'Luna'])->id,
            ])->id;
        }

        $battle->tracks()->attach($tracks);

        return $battle->load('tracks');
    }

    /**
     * @param  array{int, int, int}  $rgb
     */
    private function assertLeftCoverIs(array $rgb, Battle $battle): void
    {
        $image = $this->decode((string) Storage::get($this->service()->path($battle)));
        $color = sscanf($image->colorAt(...self::LEFT_COVER_CENTER)->toHex(), '%02x%02x%02x');

        foreach ($rgb as $channel => $value) {
            $this->assertEqualsWithDelta($value, $color[$channel], 30, 'The JPEG keeps the color of the cover, give or take');
        }
    }

    /**
     * A small square of one color, encoded as a JPEG.
     */
    private function jpeg(string $color): string
    {
        return app(ImageManager::class)->createImage(50, 50)->fill($color)->encodeUsingMediaType('image/jpeg')->toString();
    }

    private function decode(string $jpeg): ImageInterface
    {
        return app(ImageManager::class)->decodeBinary($jpeg);
    }

    private function service(): BattleShareImageService
    {
        return app(BattleShareImageService::class);
    }
}
