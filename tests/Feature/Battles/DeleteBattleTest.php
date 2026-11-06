<?php

namespace Tests\Feature\Battles;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteBattleTest extends TestCase
{
    use RefreshDatabase;

    private Battle $battle;

    private Track $track;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->track = create(Track::class, ['created_by' => auth()->id()]);
        $this->battle = create(Battle::class, [
            'battle_type' => BattleTypes::TRACK,
            'battle_state' => BattleStates::OPEN,
        ]);
        $this->battle->tracks()->save($this->track);
    }

    #[Test]
    public function it_let_delete_an_open_battle_if_user_is_participating()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertDatabaseMissing('battles', ['id' => $this->battle->id]);
    }

    #[Test]
    public function it_wont_let_delete_a_battle_if_its_not_open()
    {
        $this->battle
            ->forceFill(['battle_state' => BattleStates::IN_PROGRESS])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_delete_a_battle_if_user_is_not_participating()
    {
        $this->track->forceFill(['created_by' => create(User::class)->id])->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_delete_a_battle_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('battles.destroy', $this->battle));
    }
}
