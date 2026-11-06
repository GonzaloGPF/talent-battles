<?php

namespace Database\Seeders;

use App\Enums\ActivityTypes;
use App\Interfaces\HasCommentsInterface;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasDownloadsInterface;
use App\Interfaces\HasFavoritesInterface;
use App\Interfaces\HasLikesInterface;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Design;
use App\Models\Download;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Track;
use App\Models\User;
use App\Services\ActivityService;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ActivitySeeder extends Seeder
{
    private ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::with(['tracks', 'designs', 'likes', 'comments'])->get();

        $this->seedUserActivities($users);
        $this->seedTrackActivities($users);
        $this->seedDesignActivities($users);

        $this->updateActivityCreatedAt();
    }

    private function seedUserActivities(Collection $users): void
    {
        $users->each(function (User $user) {
            auth()->loginUsingId($user->id);

            $user->tracks->each(function (Track $track) use ($user) {
                $this->activityService->create(ActivityTypes::NEW_TRACK->value, $track, $user);
            });

            $user->designs->each(function (Design $design) use ($user) {
                $this->activityService->create(ActivityTypes::NEW_DESIGN->value, $design, $user);
            });

            $user->likes->each(function (Like $like) use ($user) {
                $this->activityService->create(ActivityTypes::DO_LIKE->value, $like, $user);
            });

            $user->comments->each(function (Comment $comment) use ($user) {
                $this->activityService->create(ActivityTypes::DO_COMMENT->value, $comment, $user);
            });
        });

        auth()->logout();
    }

    private function seedTrackActivities(Collection $users): void
    {
        $tracks = Track::with(['likes', 'downloads', 'favorites', 'comments'])->get();

        $tracks->each(fn (Track $track) => $this->createActivities($users, $track));
    }

    private function seedDesignActivities(Collection $users): void
    {
        $designs = Design::with(['likes', 'downloads', 'favorites', 'comments'])->get();

        $designs->each(fn (Design $design) => $this->createActivities($users, $design));
    }

    private function createActivities(Collection $users, HasCreatedByInterface $model): void
    {
        if ($model instanceof HasDownloadsInterface) {
            $model->downloads->each(function (Download $download) use ($model, $users) {
                auth()->loginUsingId($users->random()->id);
                $this->activityService->create(ActivityTypes::DO_DOWNLOAD->value, $model);
            });
        }

        if ($model instanceof HasLikesInterface) {
            $model->likes->each(function (Like $like) use ($model, $users) {
                auth()->loginUsingId($users->random()->id);
                $this->activityService->create(ActivityTypes::DO_LIKE->value, $model);
            });
        }

        if ($model instanceof HasCommentsInterface) {
            $model->comments->each(function (Comment $comment) use ($model, $users) {
                auth()->loginUsingId($users->random()->id);
                $this->activityService->create(ActivityTypes::DO_COMMENT->value, $model);
            });
        }

        if ($model instanceof HasFavoritesInterface) {
            $model->favorites->each(function (Favorite $favorite) use ($model, $users) {
                auth()->loginUsingId($users->random()->id);
                $this->activityService->create(ActivityTypes::DO_FAVORITE->value, $model);
            });
        }

        auth()->logout();
    }

    private function updateActivityCreatedAt(): void
    {
        $faker = Factory::create();

        Activity::query()
            ->select(['id', 'created_at'])
            ->get()
            ->each(function (Activity $activity) use ($faker) {
                $activity->forceFill(['created_at' => $faker->dateTimeBetween('-1 year')])->saveQuietly();
            });
    }
}
