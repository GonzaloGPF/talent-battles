<?php

namespace Tests\Feature\Models;

use App\Models\FavoriteList;
use App\Traits\HasCreatedBy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteListTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteList $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = create(FavoriteList::class);
    }

    #[Test]
    public function favorite_list_uses_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }
}
