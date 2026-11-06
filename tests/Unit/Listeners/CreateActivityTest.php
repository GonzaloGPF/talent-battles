<?php

namespace Tests\Unit\Listeners;

use App\Enums\ActivityTypes;
use App\Events\BattleFinished;
use App\Events\CommentCreated;
use App\Events\DownloadCreated;
use App\Events\FavoriteCreated;
use App\Events\LikeCreated;
use App\Events\OrderSuccess;
use App\Listeners\CreateActivity;
use App\Models\Battle;
use App\Models\Comment;
use App\Models\Download;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Order;
use App\Models\Track;
use App\Models\User;
use App\Services\ActivityService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateActivityTest extends TestCase
{
    #[Test]
    public function create_activity_listens_to_expected_events()
    {
        Event::fake();
        Event::assertListening(OrderSuccess::class, CreateActivity::class);
        Event::assertListening(BattleFinished::class, CreateActivity::class);
        Event::assertListening(LikeCreated::class, CreateActivity::class);
        Event::assertListening(FavoriteCreated::class, CreateActivity::class);
        Event::assertListening(CommentCreated::class, CreateActivity::class);
        Event::assertListening(DownloadCreated::class, CreateActivity::class);
    }

    #[Test]
    public function it_creates_an_activity_when_order_success_is_dispatched()
    {
        $order = new Order;

        $this->mock(ActivityService::class, function (MockInterface $mock) use ($order) {
            $mock->shouldReceive('create')
                ->once()
                ->with(ActivityTypes::DO_BUY, $order);
        });

        $event = new OrderSuccess($order);
        $listener = resolve(CreateActivity::class);
        $listener->handle($event);
    }

    #[Test]
    public function it_creates_an_activity_when_battle_finished_is_dispatched()
    {
        $owner = new User;
        $track = new Track;
        $track->setRelation('createdBy', $owner);
        $battle = new Battle;
        $battle->setRelation('winner', $track);

        $this->mock(ActivityService::class, function (MockInterface $mock) use ($battle, $owner) {
            $mock->shouldReceive('create')
                ->once()
                ->with(ActivityTypes::WIN_BATTLE, $battle, $owner);
        });

        $event = new BattleFinished($battle);
        $listener = resolve(CreateActivity::class);
        $listener->handle($event);
    }

    #[Test]
    public function it_creates_an_activity_when_like_created_is_dispatched()
    {
        $track = new Track;
        $like = new Like;
        $like->setRelation('likeable', $track);

        $this->mock(ActivityService::class, function (MockInterface $mock) use ($track) {
            $mock->shouldReceive('create')
                ->once()
                ->with(ActivityTypes::DO_LIKE, $track);
        });

        $event = new LikeCreated($like);
        $listener = resolve(CreateActivity::class);
        $listener->handle($event);
    }

    #[Test]
    public function it_creates_an_activity_when_favorite_created_is_dispatched()
    {
        $track = new Track;
        $favorite = new Favorite;
        $favorite->setRelation('favoritable', $track);

        $this->mock(ActivityService::class, function (MockInterface $mock) use ($track) {
            $mock->shouldReceive('create')
                ->once()
                ->with(ActivityTypes::DO_FAVORITE, $track);
        });

        $event = new FavoriteCreated($favorite);
        $listener = resolve(CreateActivity::class);
        $listener->handle($event);
    }

    #[Test]
    public function it_creates_an_activity_when_comment_created_is_dispatched()
    {
        $track = new Track;
        $comment = new Comment;
        $comment->setRelation('commentable', $track);

        $this->mock(ActivityService::class, function (MockInterface $mock) use ($track) {
            $mock->shouldReceive('create')
                ->once()
                ->with(ActivityTypes::DO_COMMENT, $track);
        });

        $event = new CommentCreated($comment);
        $listener = resolve(CreateActivity::class);
        $listener->handle($event);
    }

    #[Test]
    public function it_creates_an_activity_when_download_created_is_dispatched()
    {
        $track = new Track;
        $download = new Download;
        $download->setRelation('downloable', $track);

        $this->mock(ActivityService::class, function (MockInterface $mock) use ($track) {
            $mock->shouldReceive('create')
                ->once()
                ->with(ActivityTypes::DO_DOWNLOAD, $track);
        });

        $event = new DownloadCreated($download);
        $listener = resolve(CreateActivity::class);
        $listener->handle($event);
    }
}
