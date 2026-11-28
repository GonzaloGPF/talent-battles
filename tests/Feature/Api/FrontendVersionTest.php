<?php

namespace Tests\Feature\Api;

use App\Events\NewDeploy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The page, every API response and the broadcast of a deployment say which build of the frontend the server has, so
 * an open page can tell a deployment has changed it.
 */
class FrontendVersionTest extends TestCase
{
    use RefreshDatabase;

    private const MANIFEST = '{"resources/js/app.js":{"file":"assets/app-1a2b3c.js","isEntry":true}}';

    private string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publicPath = storage_path('framework/testing/public-'.Str::random(8));
        File::ensureDirectoryExists("$this->publicPath/build");
        $this->app->usePublicPath($this->publicPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    #[Test]
    public function api_responses_say_which_build_the_server_has()
    {
        File::put("$this->publicPath/build/manifest.json", self::MANIFEST);

        $this->getJson(route('company.index'))
            ->assertOk()
            ->assertHeader('X-App-Version', md5(self::MANIFEST));
    }

    #[Test]
    public function api_responses_have_no_version_without_a_build()
    {
        $this->getJson(route('company.index'))
            ->assertOk()
            ->assertHeaderMissing('X-App-Version');
    }

    #[Test]
    public function the_page_keeps_the_build_it_was_loaded_with()
    {
        File::put("$this->publicPath/build/manifest.json", self::MANIFEST);

        $this->get('/')
            ->assertOk()
            ->assertSee('window.app_version = "'.md5(self::MANIFEST).'";', false);
    }

    #[Test]
    public function a_deployment_is_broadcast_with_the_build_it_has_deployed()
    {
        Event::fake([NewDeploy::class]);
        File::put("$this->publicPath/build/manifest.json", self::MANIFEST);

        Artisan::call('notify:new_deploy');

        Event::assertDispatched(NewDeploy::class, fn (NewDeploy $event) => $event->version === md5(self::MANIFEST));
    }
}
