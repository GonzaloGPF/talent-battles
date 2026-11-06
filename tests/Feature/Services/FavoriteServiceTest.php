<?php

namespace Tests\Feature\Services;

use App\Models\FavoriteList;
use App\Models\Track;
use App\Services\FavoriteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteServiceTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->service = resolve(FavoriteService::class);
    }

    #[Test]
    public function it_can_create_a_favorite_for_a_given_model()
    {
        /** @var Track $track */
        $track = create(Track::class);

        $favorite = $this->service->create($track);

        $this->assertEquals(Track::class, $favorite->favoritable_type);
        $this->assertEquals($track->id, $favorite->favoritable_id);
        $this->assertNull($favorite->favorite_list_id);
    }

    #[Test]
    public function when_creating_a_favorite_for_a_given_model_it_can_specify_a_favorite_list()
    {
        /** @var FavoriteList $favoriteList */
        $favoriteList = create(FavoriteList::class);
        /** @var Track $track */
        $track = create(Track::class);

        $favorite = $this->service->create($track, $favoriteList->id);

        $this->assertEquals(Track::class, $favorite->favoritable_type);
        $this->assertEquals($track->id, $favorite->favoritable_id);
        $this->assertEquals($favoriteList->id, $favorite->favorite_list_id);
    }

    #[Test]
    public function when_creating_a_favorite_for_a_given_model_it_can_create_a_new_favorite_list_if_passes_a_name()
    {
        /** @var Track $track */
        $track = create(Track::class);

        $favorite = $this->service->create($track, 'some list');

        $this->assertEquals(Track::class, $favorite->favoritable_type);
        $this->assertEquals($track->id, $favorite->favoritable_id);
        $this->assertEquals('Some List', $favorite->favoriteList->name);
    }
}
