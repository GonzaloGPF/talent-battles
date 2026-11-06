<?php

namespace Tests\Unit\Listeners;

use App\Events\BattleFinished;
use App\Listeners\SetBattleWinner;
use App\Models\Battle;
use App\Services\BattleService;
use Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SetBattleWinnerTest extends TestCase
{
    private Battle $battle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->battle = new Battle;
    }

    #[Test]
    public function it_listens_to_battle_finished()
    {
        Event::fake();
        Event::assertListening(BattleFinished::class, SetBattleWinner::class);
    }

    #[Test]
    public function it_set_battle_winner_when_battle_finished_is_dispatched()
    {
        $this->mock(BattleService::class, function (MockInterface $mock) {
            $mock->shouldReceive('setWinner')
                ->once()
                ->with($this->battle);
        });

        $event = new BattleFinished($this->battle);
        $listener = resolve(SetBattleWinner::class);
        $listener->handle($event);
    }
}
