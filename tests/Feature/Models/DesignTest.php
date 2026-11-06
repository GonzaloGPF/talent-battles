<?php

namespace Tests\Feature\Models;

use App\Models\Album;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Report;
use App\Models\Vote;
use App\Traits\Commentable;
use App\Traits\Downloable;
use App\Traits\Favoritable;
use App\Traits\HasCreatedBy;
use App\Traits\HasFiles;
use App\Traits\NameFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DesignTest extends TestCase
{
    use RefreshDatabase;

    private Design $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = create(Design::class);
    }

    #[Test]
    public function design_has_file_trait()
    {
        $this->assertUsesTrait($this->model, HasFiles::class);
    }

    #[Test]
    public function design_has_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }

    #[Test]
    public function design_has_has_name_formatter_trait()
    {
        $this->assertUsesTrait($this->model, NameFormatter::class);
    }

    #[Test]
    public function design_has_like_and_favoritable_trait()
    {
        $this->assertUsesTrait($this->model, Favoritable::class);
    }

    #[Test]
    public function design_has_downloadable_trait()
    {
        $this->assertUsesTrait($this->model, Downloable::class);
    }

    #[Test]
    public function design_has_commentable_trait()
    {
        $this->assertUsesTrait($this->model, Commentable::class);
    }

    #[Test]
    public function design_belongs_to_many_battles()
    {
        $this->assertBelongsToMany($this->model, Battle::class);
    }

    #[Test]
    public function design_morphs_many_votes()
    {
        $this->assertMorphsMany($this->model, Vote::class);
    }

    #[Test]
    public function design_morphs_many_reports()
    {
        $this->assertMorphsMany($this->model, Report::class);
    }

    #[Test]
    public function design_belongs_to_an_album()
    {
        $this->assertBelongsTo($this->model, Album::class);
    }
}
