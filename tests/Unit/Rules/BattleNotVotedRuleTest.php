<?php

namespace Rules;

use App\Enums\BattleStates;
use App\Models\Battle;
use App\Models\User;
use App\Models\Vote;
use App\Rules\BattleNotVoted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BattleNotVotedRuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Battle $battle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->signIn();
        $this->battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS->value]);
    }

    #[Test]
    public function it_validates_battle_not_voted_correctly(): void
    {
        $this->assertTrue($this->isValid());

        create(Vote::class, [
            'created_by' => $this->user->id,
            'battle_id' => $this->battle->id,
        ]);

        $this->assertFalse($this->isValid());
    }

    private function isValid(): bool
    {
        $validator = Validator::make(
            ['battle_id' => $this->battle->id],
            ['battle_id' => new BattleNotVoted]
        );

        return ! $validator->fails();
    }
}
