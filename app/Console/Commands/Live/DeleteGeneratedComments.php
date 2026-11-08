<?php

namespace App\Console\Commands\Live;

use App\Models\Comment;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class DeleteGeneratedComments extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-generated-comments
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the comments that were generated from lang/*/comments.php, in any language';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $generatedContents = collect(['es', 'en'])
            ->flatMap(fn (string $locale) => array_values(trans('comments', [], $locale)))
            ->all();

        $comments = Comment::whereIn('content', $generatedContents);
        $total = $comments->count();

        $this->info("Found $total generated Comments.");

        if (! $total || ! $this->confirmToProceed()) {
            return self::SUCCESS;
        }

        $comments->delete();

        $this->info("Deleted $total Comments!");

        return self::SUCCESS;
    }
}
