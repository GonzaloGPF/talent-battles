<?php

namespace Tests\Feature\Models;

use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Traits\HasCreatedBy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    private Favorite $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(Favorite::class);
    }

    #[Test]
    public function favorite_uses_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }

    public function favorite_belongs_to_a_favorite_list()
    {
        $this->assertBelongsTo($this->model, FavoriteList::class);
    }

    #[Test]
    public function favorite_morphs_to()
    {
        $this->assertMorphsTo($this->model, 'favoritable');
    }
}
