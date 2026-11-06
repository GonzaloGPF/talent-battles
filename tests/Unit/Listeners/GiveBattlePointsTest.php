<?php

namespace Tests\Unit\Listeners;

use App\Events\BattleFinished;
use App\Listeners\GiveBattlePoints;
use App\Models\Battle;
use App\Models\Track;
use App\Services\PointsService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GiveBattlePointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->battle = new Battle;
    }

    #[Test]
    public function it_listens_to_battle_finished()
    {
        Event::fake();
        Event::assertListening(BattleFinished::class, GiveBattlePoints::class);
    }

    #[Test]
    public function it_gives_draw_points_when_battle_finishes_with_no_winner()
    {
        $this->mock(PointsService::class, function (MockInterface $mock) {
            $mock->shouldReceive('giveDrawPoints')
                ->once()
                ->with($this->battle);
        });

        $event = new BattleFinished($this->battle);
        $listener = resolve(GiveBattlePoints::class);
        $listener->handle($event);
    }

    #[Test]
    public function it_give_points_to_winner_and_voters_when_battle_finishes_with_a_winner()
    {
        $this->battle->setRelation('winner', new Track);

        $this->mock(PointsService::class, function (MockInterface $mock) {
            $mock->shouldReceive('givePointsToWinner')
                ->once()
                ->with($this->battle);

            $mock->shouldReceive('givePointsToVoters')
                ->once()
                ->with($this->battle);
        });

        $event = new BattleFinished($this->battle);
        $listener = resolve(GiveBattlePoints::class);
        $listener->handle($event);
    }
}
