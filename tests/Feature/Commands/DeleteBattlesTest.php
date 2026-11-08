<?php

namespace Tests\Feature\Commands;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\TrackTypes;
use App\Helpers\NotificationHelper;
use App\Models\Activity;
use App\Models\Battle;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;
use App\Notifications\BattleWon;
use App\Services\VoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteBattlesTest extends TestCase
{
    use RefreshDatabase;

    private const OLD = '2023-06-16 08:05:03';

    private const NEW = '2026-11-08 17:00:00';

    #[Test]
    public function it_deletes_the_battles_created_before_the_date_with_their_votes()
    {
        $oldBattle = $this->createBattle(self::OLD);
        $vote = $this->vote($oldBattle, $oldBattle->tracks->first());
        $newBattle = $this->createBattle(self::NEW);

        $this->artisan('app:delete-battles', ['before' => '2026-11-08'])
            ->expectsOutput('1 Battles were created before 2026-11-08 00:00:00, 0 of them finished.')
            ->expectsOutput('Deleted 1 Battles!')
            ->assertSuccessful();

        $this->assertModelMissing($oldBattle);
        $this->assertModelMissing($vote);
        $this->assertModelExists($newBattle);
    }

    #[Test]
    public function it_deletes_what_points_at_the_deleted_battles()
    {
        $oldBattle = $this->createBattle(self::OLD);
        $newBattle = $this->createBattle(self::NEW);
        $user = create(User::class);
        $traces = [];

        foreach ([$oldBattle, $newBattle] as $battle) {
            $traces[$battle->id] = [
                create(Comment::class, ['commentable_type' => Battle::class, 'commentable_id' => $battle->id]),
                create(Like::class, ['likeable_type' => Battle::class, 'likeable_id' => $battle->id]),
                create(Favorite::class, ['favoritable_type' => Battle::class, 'favoritable_id' => $battle->id]),
                create(Activity::class, ['target_type' => Battle::class, 'target_id' => $battle->id]),
            ];
            $user->notify(new BattleWon($battle, $user));
        }

        $this->artisan('app:delete-battles', ['before' => '2026-11-08'])
            ->assertSuccessful();

        foreach ($traces[$oldBattle->id] as $trace) {
            $this->assertModelMissing($trace);
        }

        foreach ($traces[$newBattle->id] as $trace) {
            $this->assertModelExists($trace);
        }

        $this->assertEquals(
            [NotificationHelper::notyId($newBattle->id, BattleWon::class)],
            $user->notifications()->get()->pluck('data.noty_id')->all(),
        );
    }

    #[Test]
    public function it_takes_back_what_finishing_a_won_battle_gave()
    {
        $battle = $this->createBattle(self::OLD);
        $winnerTrack = $battle->tracks->first();
        $voter = create(User::class, ['rating' => 0]);
        $this->vote($battle, $winnerTrack, $voter);
        $users = $battle->getParticipants()->push($voter);
        $before = $this->standing($users);

        $battle->changeState(BattleStates::FINISHED);
        $this->assertNotEquals($before, $this->standing($users));

        $this->artisan('app:delete-battles', ['before' => '2026-11-08'])
            ->expectsOutput('1 Battles were created before 2026-11-08 00:00:00, 1 of them finished.')
            ->assertSuccessful();

        $this->assertEquals($before, $this->standing($users));
    }

    #[Test]
    public function it_takes_back_what_finishing_a_draw_gave()
    {
        $battle = $this->createBattle(self::OLD);
        $users = $battle->getParticipants();
        $before = $this->standing($users);

        $battle->changeState(BattleStates::FINISHED);
        $this->assertNotEquals($before, $this->standing($users));

        $this->artisan('app:delete-battles', ['before' => '2026-11-08'])
            ->assertSuccessful();

        $this->assertEquals($before, $this->standing($users));
    }

    /**
     * A track battle in progress between two beats of different owners.
     */
    private function createBattle(string $createdAt): Battle
    {
        $battle = create(Battle::class, [
            'battle_state' => BattleStates::IN_PROGRESS->value,
            'battle_type' => BattleTypes::TRACK,
            'created_at' => $createdAt,
        ]);

        $battle->tracks()->attach([
            create(Track::class, ['created_by' => create(User::class, ['rating' => 0])->id, 'track_type' => TrackTypes::BEAT])->id,
            create(Track::class, ['created_by' => create(User::class, ['rating' => 0])->id, 'track_type' => TrackTypes::BEAT])->id,
        ]);

        return $battle->load('tracks');
    }

    private function vote(Battle $battle, Track $track, ?User $voter = null): Vote
    {
        return resolve(VoteService::class)->create($battle->id, $track, $voter ?? create(User::class));
    }

    /**
     * What finishing a battle changes for each user: points, experience and rating.
     *
     * @param  Collection<int, User>  $users
     * @return array<int, array<int, float>>
     */
    private function standing(Collection $users): array
    {
        return $users
            ->map(fn (User $user) => $user->fresh())
            ->map(fn (User $user) => [(float) $user->points, (float) $user->experience, (float) $user->rating])
            ->all();
    }
}
