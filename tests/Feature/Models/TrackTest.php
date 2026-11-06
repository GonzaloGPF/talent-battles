<?php

namespace Tests\Feature\Models;

use App\Models\Album;
use App\Models\Battle;
use App\Models\Comment;
use App\Models\Download;
use App\Models\Favorite;
use App\Models\File;
use App\Models\Like;
use App\Models\Report;
use App\Models\Track;
use App\Traits\Commentable;
use App\Traits\Downloable;
use App\Traits\Favoritable;
use App\Traits\HasCreatedBy;
use App\Traits\HasFiles;
use App\Traits\NameFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackTest extends TestCase
{
    use RefreshDatabase;

    private Track $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(Track::class);
    }

    #[Test]
    public function track_has_file_trait()
    {
        $this->assertUsesTrait($this->model, HasFiles::class);
    }

    #[Test]
    public function track_has_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }

    #[Test]
    public function track_has_has_name_formatter_trait()
    {
        $this->assertUsesTrait($this->model, NameFormatter::class);
    }

    #[Test]
    public function track_has_like_and_favoritable_trait()
    {
        $this->assertUsesTrait($this->model, Favoritable::class);
    }

    #[Test]
    public function track_has_downloadable_trait()
    {
        $this->assertUsesTrait($this->model, Downloable::class);
    }

    #[Test]
    public function track_has_commentable_trait()
    {
        $this->assertUsesTrait($this->model, Commentable::class);
    }

    #[Test]
    public function track_belongs_to_many_battles()
    {
        $this->assertBelongsToMany($this->model, Battle::class);
    }

    #[Test]
    public function track_belongs_to_an_album()
    {
        $this->assertBelongsTo($this->model, Album::class);
    }

    #[Test]
    public function track_morph_many_downloads()
    {
        $this->assertMorphsMany($this->model, Download::class);
    }

    #[Test]
    public function track_morph_many_likes()
    {
        $this->assertMorphsMany($this->model, Like::class);
    }

    #[Test]
    public function track_morph_many_favorites()
    {
        $this->assertMorphsMany($this->model, Favorite::class);
    }

    #[Test]
    public function track_morph_one_audio_file()
    {
        $this->assertMorphsOne($this->model, File::class, 'audio');
    }

    #[Test]
    public function track_morph_many_comments()
    {
        $this->assertMorphsMany($this->model, Comment::class);
    }

    #[Test]
    public function track_morphs_many_reports()
    {
        $this->assertMorphsMany($this->model, Report::class);
    }
}
