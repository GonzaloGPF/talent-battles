<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Traits\HasCreatedBy;
use App\Traits\HasFiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private Comment $model;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->model = make(Comment::class);
    }

    #[Test]
    public function comment_uses_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }

    #[Test]
    public function comment_users_has_files_trait()
    {
        $this->assertUsesTrait($this->model, HasFiles::class);
    }

    #[Test]
    public function comment_morphs_to()
    {
        $this->assertMorphsTo($this->model, 'commentable');
    }
}
