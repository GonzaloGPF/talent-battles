<?php

namespace Tests\Feature\Services;

use App\Enums\BattleStates;
use App\Enums\Levels;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = create(User::class, [
            'experience' => 0,
        ]);
        $this->service = resolve(UserService::class);
    }

    #[Test]
    public function it_knows_how_to_get_the_correct_level_depending_on_user_experience()
    {
        $this->assertEquals(Levels::NOVICE->value, $this->service->getLevel(0));

        $this->assertEquals(Levels::NOVICE->value, $this->service->getLevel(10));

        $this->assertEquals(Levels::MEDIUM->value, $this->service->getLevel(1500));

        $this->assertEquals(Levels::ADVANCED->value, $this->service->getLevel(5800));
    }

    #[Test]
    public function it_know_how_to_calculate_user_rating()
    {
        /** @var Track $creation */
        $creation = create(Track::class, ['created_by' => $this->user->id]);

        /** @var Battle $wonBattle */
        $wonBattle = create(Battle::class, [
            'won_by' => $this->user->id,
            'battle_state' => BattleStates::FINISHED,
        ]);
        /** @var Battle $lostBattle1 */
        $lostBattle1 = create(Battle::class, [
            'won_by' => create(User::class)->id,
            'battle_state' => BattleStates::FINISHED,
        ]);
        /** @var Battle $lostBattle2 */
        $lostBattle2 = create(Battle::class, [
            'won_by' => create(User::class)->id,
            'battle_state' => BattleStates::FINISHED,
        ]);

        $creation->battles()->saveMany(([$wonBattle, $lostBattle1, $lostBattle2]));

        $this->assertEquals(33, $this->service->getRating($this->user));
    }

    #[Test]
    public function accounts_created_through_oauth_start_with_the_initial_points()
    {
        $user = $this->service->createFromOAuth('google', $this->oAuthUser('new@email.com'));

        $this->assertEquals(config('defaults.initial_points'), $user->fresh()->points);
    }

    #[Test]
    public function linking_an_existing_account_to_oauth_keeps_its_points()
    {
        $existingUser = create(User::class, ['email' => 'existing@email.com']);
        $existingUser->forceFill(['points' => 3])->saveQuietly();

        $this->service->createFromOAuth('google', $this->oAuthUser('existing@email.com'));

        $this->assertEquals(3, $existingUser->fresh()->points);
    }

    private function oAuthUser(string $email): SocialiteUser
    {
        return (new SocialiteUser)->map([
            'id' => '123456',
            'name' => 'OAuth User',
            'nickname' => 'oauth',
            'email' => $email,
        ]);
    }
}
