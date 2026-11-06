<?php

namespace Tests\Unit\Listeners;

use App\Enums\BattleTypes;
use App\Events\BattleFinished;
use App\Events\BattleInProgress;
use App\Events\CommentCreated;
use App\Events\DownloadCreated;
use App\Events\FavoriteCreated;
use App\Events\LikeCreated;
use App\Events\OrderSuccess;
use App\Events\SalesCreated;
use App\Listeners\CreateNotification;
use App\Models\Battle;
use App\Models\Comment;
use App\Models\Design;
use App\Models\Download;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Track;
use App\Models\User;
use App\Notifications\BattleDraw;
use App\Notifications\BattleLost;
use App\Notifications\BattleStarted;
use App\Notifications\BattleWon;
use App\Notifications\CommentReceived;
use App\Notifications\DesignSold;
use App\Notifications\DownloadReceived;
use App\Notifications\FavoriteReceived;
use App\Notifications\LikeReceived;
use App\Notifications\OrderCompleted;
use App\Notifications\TrackSold;
use Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    #[Test]
    public function create_notification_listens_to_expected_events()
    {
        Event::fake();
        Event::assertListening(OrderSuccess::class, CreateNotification::class);
        Event::assertListening(BattleFinished::class, CreateNotification::class);
        Event::assertListening(LikeCreated::class, CreateNotification::class);
        Event::assertListening(FavoriteCreated::class, CreateNotification::class);
        Event::assertListening(CommentCreated::class, CreateNotification::class);
        Event::assertListening(DownloadCreated::class, CreateNotification::class);
    }

    #[Test]
    public function it_creates_a_notification_when_order_success_is_dispatched()
    {
        $user = new User;
        $order = new Order;
        $order->setRelation('createdBy', $user);

        $event = new OrderSuccess($order);
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, function (OrderCompleted $notification) use ($order) {
            return $notification->order->id === $order->id;
        });
    }

    #[Test]
    public function it_creates_a_notification_when_sales_created_is_dispatched_with_a_track()
    {
        $user = new User;
        $track = new Track;
        $track->setRelation('createdBy', $user);
        $sale = new Sale;
        $product = new Product;
        $product->setRelations([
            'createdBy' => $user,
            'orderable' => $track,
        ]);
        $sale->setRelations([
            'product' => $product,
            'createdBy' => $user,
        ]);

        $event = new SalesCreated(collect([$sale]));
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, function (TrackSold $notification) use ($track) {
            return $notification->track->id === $track->id;
        });
    }

    #[Test]
    public function it_creates_a_notification_when_sales_created_is_dispatched_with_a_design()
    {
        $user = new User;
        $design = new Design;
        $design->setRelation('createdBy', $user);
        $sale = new Sale;
        $product = new Product;
        $product->setRelations([
            'createdBy' => $user,
            'orderable' => $design,
        ]);
        $sale->setRelations([
            'product' => $product,
            'createdBy' => $user,
        ]);

        $event = new SalesCreated(collect([$sale]));
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, function (DesignSold $notification) use ($design) {
            return $notification->design->id === $design->id;
        });
    }

    #[Test]
    public function it_creates_a_notification_when_battle_in_progress_is_dispatched()
    {
        $user1 = new User;
        $track1 = new Track;
        $track1->setRelation('createdBy', $user1);

        $user2 = new User;
        $track2 = new Track;
        $track2->setRelation('createdBy', $user2);

        $battle = new Battle;
        $battle->battle_type = BattleTypes::TRACK;
        $battle->setRelation('tracks', collect([$track1, $track2]));

        $event = new BattleInProgress($battle);
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user1, function (BattleStarted $notification) use ($battle) {
            return $notification->battle->id === $battle->id;
        });

        Notification::assertSentTo($user2, function (BattleStarted $notification) use ($battle) {
            return $notification->battle->id === $battle->id;
        });
    }

    #[Test]
    public function it_creates_a_battle_won_and_battle_lost_notifications_when_battle_finished_is_dispatched()
    {
        $winnerUser = new User;
        $winnerUser->id = 1;
        $winnerTrack = new Track;
        $winnerTrack->setRelation('createdBy', $winnerUser);

        $loserUser = new User;
        $loserUser->id = 2;
        $loserTrack = new Track;
        $loserTrack->setRelation('createdBy', $loserUser);

        $battle = new Battle;
        $battle->battle_type = BattleTypes::TRACK;
        $battle->setRelations([
            'winner' => $winnerTrack,
            'tracks' => collect([$winnerTrack, $loserTrack]),
        ]);

        $event = new BattleFinished($battle);
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($winnerUser, function (BattleWon $notification) use ($battle) {
            return $notification->battle->id === $battle->id;
        });

        Notification::assertSentTo($loserUser, function (BattleLost $notification) use ($battle) {
            return $notification->battle->id === $battle->id;
        });
    }

    #[Test]
    public function it_creates_a_battle_draw_notifications_when_battle_finished_is_dispatched_without_winner()
    {
        $user1 = new User;
        $user1->id = 1;
        $track1 = new Track;
        $track1->setRelation('createdBy', $user1);

        $user2 = new User;
        $user2->id = 2;
        $track2 = new Track;
        $track2->setRelation('createdBy', $user2);

        $battle = new Battle;
        $battle->battle_type = BattleTypes::TRACK;
        $battle->setRelations([
            'winner' => null,
            'tracks' => collect([$track1, $track2]),
        ]);

        $event = new BattleFinished($battle);
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user1, function (BattleDraw $notification) use ($battle) {
            return $notification->battle->id === $battle->id;
        });

        Notification::assertSentTo($user2, function (BattleDraw $notification) use ($battle) {
            return $notification->battle->id === $battle->id;
        });
    }

    #[Test]
    public function it_creates_a_notification_when_like_created_is_dispatched()
    {
        $owner = new User;
        $track = new Track;
        $track->setRelation('createdBy', $owner);
        $like = new Like;
        $like->setRelation('likeable', $track);

        $event = new LikeCreated($like);
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($owner, function (LikeReceived $notification) use ($like) {
            return $notification->like->id === $like->id;
        });
    }

    #[Test]
    public function it_creates_a_notification_when_favorite_created_is_dispatched()
    {
        $owner = new User;
        $track = new Track;
        $track->setRelation('createdBy', $owner);
        $favorite = new Favorite;
        $favorite->setRelation('favoritable', $track);

        $event = new FavoriteCreated($favorite);
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($owner, function (FavoriteReceived $notification) use ($favorite) {
            return $notification->favorite->id === $favorite->id;
        });
    }

    #[Test]
    public function it_creates_a_notification_when_comment_created_is_dispatched()
    {
        $owner = new User;
        $track = new Track;
        $track->setRelation('createdBy', $owner);
        $comment = new Comment;
        $comment->setRelation('commentable', $track);

        $event = new CommentCreated($comment);
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($owner, function (CommentReceived $notification) use ($comment) {
            return $notification->comment->id === $comment->id;
        });
    }

    #[Test]
    public function it_creates_a_notification_when_download_created_is_dispatched()
    {
        $owner = new User;
        $track = new Track;
        $track->setRelation('createdBy', $owner);
        $download = new Download;
        $download->setRelation('downloable', $track);

        $event = new DownloadCreated($download);
        $listener = resolve(CreateNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($owner, function (DownloadReceived $notification) use ($download) {
            return $notification->download->id === $download->id;
        });
    }
}
