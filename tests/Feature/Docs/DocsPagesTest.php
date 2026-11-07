<?php

namespace Tests\Feature\Docs;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocsPagesTest extends TestCase
{
    public static function linkedFromTheApp(): array
    {
        return [
            'es home' => ['/docs/es'],
            'en home' => ['/docs/en'],
            'es conditions' => ['/docs/es/conditions'],
            'en conditions' => ['/docs/en/conditions'],
            'es privacy' => ['/docs/es/privacy'],
            'en privacy' => ['/docs/en/privacy'],
            'es cookies' => ['/docs/es/cookies'],
            'en cookies' => ['/docs/en/cookies'],
        ];
    }

    #[Test]
    #[DataProvider('linkedFromTheApp')]
    public function docs_are_served_by_larecipe_and_not_by_the_spa_catch_all(string $url)
    {
        $this->get($url)
            ->assertOk()
            ->assertDontSee('id="app"', false)
            ->assertDontSee('{{', false);
    }

    #[Test]
    public function docs_images_are_served()
    {
        // Nginx does not hand image requests to Laravel, so they have to be real files in public
        $this->get('/docs/es/technology')->assertSee('https://talentbattles.com/images/technologies/php.webp', false);

        $this->assertFileExists(public_path('images/technologies/php.webp'));
        $this->assertFileExists(public_path('images/logo_big_bg.webp'));
    }

    #[Test]
    public function docs_tables_are_rendered()
    {
        $this->get('/docs/es/technology')->assertSee('<table', false);
        $this->get('/docs/en/technology')->assertSee('<table', false);
    }

    #[Test]
    public function unknown_docs_pages_are_not_found()
    {
        $this->get('/docs/es/this-page-does-not-exist')->assertNotFound();
    }

    #[Test]
    public function the_spa_still_answers_any_other_url_with_a_404()
    {
        $this->get('/some/frontend/route')
            ->assertNotFound()
            ->assertSee('id="app"', false);
    }
}
