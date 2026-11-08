<?php

namespace Tests\Feature\Commands;

use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteGeneratedCommentsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_the_comments_generated_in_any_language()
    {
        $spanishComment = create(Comment::class, ['content' => trans('comments.comment_1', [], 'es')]);
        $englishComment = create(Comment::class, ['content' => trans('comments.comment_2', [], 'en')]);
        $writtenComment = create(Comment::class, ['content' => 'Me encanta el bajo de este beat']);

        $this->artisan('app:delete-generated-comments')
            ->expectsOutput('Found 2 generated Comments.')
            ->expectsOutput('Deleted 2 Comments!')
            ->assertSuccessful();

        $this->assertModelMissing($spanishComment);
        $this->assertModelMissing($englishComment);
        $this->assertModelExists($writtenComment);
    }
}
