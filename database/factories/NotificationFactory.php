<?php

namespace Database\Factories;

use App\Helpers\NotificationHelper;
use App\Interfaces\NotyInterface;
use App\Models\Comment;
use App\Models\Download;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\CommentReceived;
use App\Notifications\DownloadReceived;
use App\Notifications\FavoriteReceived;
use App\Notifications\LikeReceived;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $helper = resolve(NotificationHelper::class);

        $type = $this->faker->randomElement([
            LikeReceived::class,
            FavoriteReceived::class,
            DownloadReceived::class,
            CommentReceived::class,
        ]);

        /** @var NotyInterface $noty */
        $noty = resolve($type, $this->getParams($type));

        return [
            'id' => uniqid(),
            'type' => "App\\Notifications\\$type",
            'notifiable_type' => User::class,
            'notifiable_id' => associateTo(User::class),
            'data' => $helper->toArray($noty),
        ];
    }

    private function getParams($type): ?array
    {
        return match ($type) {
            LikeReceived::class => ['like' => Like::find(associateTo(Like::class))],
            FavoriteReceived::class => ['favorite' => Favorite::find(associateTo(Favorite::class))],
            DownloadReceived::class => ['download' => Download::find(associateTo(Download::class))],
            CommentReceived::class => ['comment' => Comment::find(associateTo(Comment::class))],
        };
    }
}
