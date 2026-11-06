<?php

namespace Tests\Feature\Battles;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\DesignTypes;
use App\Models\Battle;
use App\Models\Design;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateDesignBattleTest extends TestCase
{
    use RefreshDatabase;

    private Design $design;

    private Design $otherDesign;

    private User $user;

    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        User::flushEventListeners();
        $this->user = create(User::class, [
            'points' => config('company.points.prices.create_battle'),
            'experience' => 0,
        ]);
        $this->design = create(Design::class, [
            'created_by' => $this->user->id,
            'can_challenge' => true,
            'design_type' => DesignTypes::LOGO,
        ]);
        $this->otherUser = create(User::class);
        $this->otherDesign = create(Design::class, [
            'created_by' => $this->otherUser->id,
            'can_challenge' => true,
            'design_type' => DesignTypes::LOGO,
        ]);
        $this->signIn($this->user);
    }

    #[Test]
    public function users_can_create_battles_for_a_design()
    {
        $this->makeRequest()
            ->assertSuccessful();

        /** @var Battle $battle */
        $battle = Battle::first();
        $this->assertEquals(BattleStates::OPEN->value, $battle->battle_state);
        $this->assertEquals(BattleTypes::DESIGN, $battle->battle_type);
        $this->assertCount(1, $battle->designs);

        $this->assertTrue($this->design->battles->contains($battle->id));
    }

    #[Test]
    public function when_creating_a_battle_for_a_design_it_will_decrease_the_user_points_correctly()
    {
        $battlePoints = config('company.points.prices.create_battle');

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals(0, $this->user->points);
        $this->assertEquals($battlePoints / 2, $this->user->experience);
    }

    #[Test]
    public function when_creating_a_battle_for_a_design_it_can_find_a_battle_already_created()
    {
        /** @var Design $design */
        $design = create(Design::class, [
            'design_type' => $this->design->design_type,
            'created_by' => create(User::class)->id,
        ]);

        /** @var Battle $battle */
        $battle = create(Battle::class, [
            'battle_state' => BattleStates::OPEN,
            'battle_type' => BattleTypes::DESIGN,
        ]);
        $battle->designs()->save($design);

        $this->makeRequest()
            ->assertSuccessful();

        $battle = Battle::first();
        $this->assertEquals(BattleStates::IN_PROGRESS->value, $battle->battle_state);
        $this->assertCount(2, $battle->designs);

        $this->assertTrue($design->battles->contains($battle->id));
    }

    #[Test]
    public function when_creating_a_battle_for_a_design_it_wont_use_a_battle_already_created_if_has_different_design_type()
    {
        $this->design->forceFill([
            'design_type' => DesignTypes::LOGO,
        ])->saveQuietly();

        /** @var Design $design */
        $design = create(Design::class, [
            'design_type' => DesignTypes::FONT,
            'created_by' => create(User::class)->id,
        ]);

        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);
        $battle->designs()->save($design);

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(2, Battle::all());
    }

    #[Test]
    public function when_creating_a_battle_for_a_design_it_can_use_an_empty_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertTrue($this->design->battles->contains($battle->id));
    }

    #[Test]
    public function when_creating_a_battle_for_a_design_it_wont_let_battles_with_owns_designs_it_will_create_another_battle_instead()
    {
        /** @var Design $design */
        $design = create(Design::class, ['created_by' => $this->user->id]);
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);
        $battle->designs()->save($design);

        $this->makeRequest()
            ->assertSuccessful();

        $battles = Battle::all();
        $this->assertCount(2, $battles);
        $this->assertCount(1, $battles->first()->designs);
        $this->assertCount(1, $battles->last()->designs);
    }

    #[Test]
    public function when_creating_a_battle_you_can_specify_another_design_to_challenge_with()
    {
        $this->makeRequest(['opponent_id' => $this->otherDesign->id])
            ->assertSuccessful();

        /** @var Battle $battle */
        $battle = Battle::first();
        $this->assertEquals(BattleStates::IN_PROGRESS->value, $battle->battle_state);
        $this->assertEquals(BattleTypes::DESIGN, $battle->battle_type);
        $this->assertCount(2, $battle->designs);

        $this->assertTrue($battle->designs->contains($this->design->id));
        $this->assertTrue($battle->designs->contains($this->otherDesign->id));
    }

    #[Test]
    public function when_creating_a_battle_specifying_another_design_it_wont_accept_if_other_design_cannot_be_challenged()
    {
        $this->otherDesign
            ->forceFill(['can_challenge' => false])
            ->saveQuietly();

        $this->makeRequest(['opponent_id' => $this->otherDesign->id])
            ->assertUnprocessable();
    }

    #[Test]
    public function when_creating_a_battle_specifying_another_design_it_wont_accept_if_other_design_belongs_to_the_logged_user()
    {
        $this->otherDesign
            ->forceFill(['created_by' => $this->user->id])
            ->saveQuietly();

        $this->makeRequest(['opponent_id' => $this->otherDesign->id])
            ->assertUnprocessable();
    }

    #[Test]
    public function when_creating_a_battle_specifying_another_design_it_wont_accept_if_other_design_has_different_type()
    {
        $this->otherDesign
            ->forceFill(['design_type' => DesignTypes::FREE])
            ->saveQuietly();

        $this->makeRequest(['opponent_id' => $this->otherDesign->id])
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_design_that_is_already_looking_for_a_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);
        $battle->designs()->save($this->design);

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_design_that_is_already_fighting_in_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS]);
        $battle->designs()->save($this->design);

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_design_if_design_cannot_be_challenged()
    {
        $this->design
            ->forceFill(['can_challenge' => false])
            ->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_design_of_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        $this->design->created_by = $otherUser->id;
        $this->design->save();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_for_a_design_that_is_already_in_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, [
            'battle_state' => BattleStates::IN_PROGRESS,
            'battle_type' => BattleTypes::DESIGN,
        ]);
        /** @var Design $foreignDesign */
        $foreignDesign = create(Design::class, [
            'created_by' => create(User::class)->id,
            'design_type' => DesignTypes::LOGO,
            'can_challenge' => true,
        ]);
        $battle->designs()->saveMany([$this->otherDesign, $foreignDesign]);

        $this->makeRequest(['opponent_id' => $this->otherDesign->id])
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_battle_to_non_authenticated_user()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    #[Test]
    public function it_wont_let_create_a_battle_if_user_has_not_enough_points()
    {
        $this->user->forceFill([
            'points' => 0,
        ])->saveQuietly();

        $this->makeRequest()
            ->assertUnprocessable();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('battles.store'), array_merge([
            'id' => $this->design->id,
            'type' => 'design',
        ], $attributes));
    }
}
