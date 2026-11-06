<?php

namespace App\Console\Commands\Live;

use App\Enums\Roles;
use App\Enums\TrackTypes;
use App\Models\Comment;
use App\Models\Track;
use App\Models\User;
use Faker\Factory;
use Illuminate\Console\Command;

class CreateComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-comments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create some random comments';

    public array $commentContents = [];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $totalComments = count(include 'lang/es/comments.php');

        $users = User::where('role', Roles::USER)->whereHas('beats');

        $beats = Track::where('track_type', TrackTypes::BEAT)
            ->get();

        $beats->each(function (Track $beat) use ($totalComments, $users) {
            $user = $users->where('id', '!=', $beat->created_by)
                ->inRandomOrder()
                ->first();

            if (! $user) {
                return;
            }

            $commentIndex = rand(1, $totalComments);

            app()->setLocale($user->locale);

            $content = trans("comments.comment_$commentIndex");

            //             if (in_array($content, $this->commentContents)) {
            //                 return;
            //             }

            $this->commentContents[] = $content;

            $comment = new Comment;
            $createdAt = Factory::create()->dateTimeBetween('-8 months');

            $comment->forceFill([
                'content' => $content,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $comment
                ->commentable()
                ->associate($beat);

            $comment->createdBy()
                ->associate($user);

            $comment->save();
        });

        $totalComments = count($this->commentContents);

        $this->info("Wrote $totalComments Comments for {$beats->count()} Beats!");
    }
}
