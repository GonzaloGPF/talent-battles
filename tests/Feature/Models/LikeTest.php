<?php

namespace Tests\Feature\Models;

use App\Models\Like;
use App\Traits\HasCreatedBy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    private Like $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(Like::class);
    }

    #[Test]
    public function likes_uses_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }

    #[Test]
    public function like_morphs_to()
    {
        $this->assertMorphsTo($this->model, 'likeable');
    }
}
