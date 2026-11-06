<?php

namespace Tests\Unit\Listeners;

use App\Enums\BattleTypes;
use App\Events\BattleFinished;
use App\Listeners\UpdateUserRating;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use App\Services\UserService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateUserRatingTest extends TestCase
{
    private Battle $battle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->battle = new Battle;
        $this->battle->battle_type = BattleTypes::TRACK;
    }

    #[Test]
    public function it_listens_to_battle_finished()
    {
        Event::fake();
        Event::assertListening(BattleFinished::class, UpdateUserRating::class);
    }

    #[Test]
    public function it_updates_user_rating_of_all_participants_when_battle_finished_is_dispatched()
    {
        $user = new User;
        $track = new Track;

        $track->setRelation('createdBy', $user);
        $this->battle->setRelation('tracks', collect([$track]));

        $this->mock(UserService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('updateRating')
                ->once()
                ->with($user);
        });

        $event = new BattleFinished($this->battle);
        $listener = resolve(UpdateUserRating::class);
        $listener->handle($event);
    }
}
