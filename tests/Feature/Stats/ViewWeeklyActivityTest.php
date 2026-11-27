<?php

namespace Tests\Feature\Stats;

use App\Enums\Roles;
use App\Models\Album;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewWeeklyActivityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_staff_sees_what_real_users_did_each_week()
    {
        // Before the last 8 weeks: the accounts and what the counted records hang from
        $this->travelTo('2026-09-01 10:00');
        $user = create(User::class, ['role' => Roles::USER]);
        $staff = create(User::class, ['role' => Roles::ADMIN]);
        create(Album::class, ['created_by' => $user->id]);
        $battle = create(Battle::class, ['created_by' => $staff->id]);
        $track = create(Track::class, ['created_by' => $user->id]);
        create(Design::class, ['created_by' => $user->id]);

        // The week of 2026-11-16
        $this->travelTo('2026-11-17 10:00');
        create(User::class, ['role' => Roles::USER], 2);
        create(Vote::class, ['created_by' => $user->id, 'battle_id' => $battle->id, 'votable_id' => $track->id, 'votable_type' => Track::class]);
        create(Vote::class, ['created_by' => $staff->id, 'battle_id' => $battle->id, 'votable_id' => $track->id, 'votable_type' => Track::class]);
        create(Battle::class, ['created_by' => $user->id]);
        create(Battle::class, ['created_by' => $staff->id]);
        create(Track::class, ['created_by' => $user->id]);
        create(Design::class, ['created_by' => $user->id]);
        create(Track::class, ['created_by' => $staff->id]);

        // This week, which starts on Monday 2026-11-23
        $this->travelTo('2026-11-26 10:00');
        create(User::class, ['role' => Roles::MANAGER]);
        create(User::class, ['role' => Roles::USER])->delete();

        $weeks = $this->actingAs($staff)
            ->getJson(route('stats.weekly_activity'))
            ->assertOk()
            ->json('data.weeks');

        $this->assertCount(8, $weeks);
        $this->assertEquals(
            ['week' => '2026-11-23', 'registrations' => 1, 'votes' => 0, 'user_battles' => 0, 'paired_battles' => 0, 'creations' => 0],
            $weeks[0],
        );
        $this->assertEquals(
            ['week' => '2026-11-16', 'registrations' => 2, 'votes' => 1, 'user_battles' => 1, 'paired_battles' => 1, 'creations' => 2],
            $weeks[1],
        );
        $this->assertSame('2026-10-05', $weeks[7]['week']);
    }

    #[Test]
    public function managers_see_it_too()
    {
        $this->actingAs(create(User::class, ['role' => Roles::MANAGER]))
            ->getJson(route('stats.weekly_activity'))
            ->assertOk();
    }

    #[Test]
    public function users_cannot_see_it()
    {
        $this->actingAs(create(User::class, ['role' => Roles::USER]))
            ->getJson(route('stats.weekly_activity'))
            ->assertForbidden();
    }

    #[Test]
    public function guests_cannot_see_it()
    {
        $this->getJson(route('stats.weekly_activity'))
            ->assertUnauthorized();
    }
}
