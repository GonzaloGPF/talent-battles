<?php

namespace Tests\Feature\Models;

use App\Models\Album;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Report;
use App\Models\Track;
use App\Traits\HasFiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    use RefreshDatabase;

    private Album $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(Album::class);
    }

    #[Test]
    public function album_has_many_creations()
    {
        $this->assertHasMany($this->model, Track::class);
    }

    #[Test]
    public function album_uses_files_trait()
    {
        $this->assertUsesTrait($this->model, HasFiles::class);
    }

    #[Test]
    public function album_morph_many_likes()
    {
        $this->assertMorphsMany($this->model, Like::class);
    }

    #[Test]
    public function album_morph_many_favorites()
    {
        $this->assertMorphsMany($this->model, Favorite::class);
    }

    #[Test]
    public function album_morphs_many_reports()
    {
        $this->assertMorphsMany($this->model, Report::class);
    }
}
