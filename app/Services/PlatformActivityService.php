<?php

namespace App\Services;

use App\Enums\Roles;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * What real users do on Talent Battles, week by week. Staff accounts (admins and managers) are left out, and the
 * battles that Talent Battles pairs with its own account are counted apart.
 */
class PlatformActivityService
{
    /**
     * The last weeks, newest first. A week starts on Monday.
     *
     * @return list<array{week: string, registrations: int, votes: int, user_battles: int, paired_battles: int, creations: int}>
     */
    public function weekly(int $weeks = 8): array
    {
        $since = now()->startOfWeek()->subWeeks($weeks - 1);
        $userIds = User::withTrashed()->where('role', Roles::USER)->select('id');

        $registrations = $this->perWeek(User::withTrashed()->where('role', Roles::USER), $since);
        $votes = $this->perWeek(Vote::whereIn('created_by', $userIds), $since);
        $userBattles = $this->perWeek(Battle::whereIn('created_by', $userIds), $since);
        $pairedBattles = $this->perWeek(Battle::whereNotIn('created_by', $userIds), $since);
        $tracks = $this->perWeek(Track::whereIn('created_by', $userIds), $since);
        $designs = $this->perWeek(Design::whereIn('created_by', $userIds), $since);

        return array_map(function (int $weeksAgo) use ($registrations, $votes, $userBattles, $pairedBattles, $tracks, $designs) {
            $week = now()->startOfWeek()->subWeeks($weeksAgo)->toDateString();

            return [
                'week' => $week,
                'registrations' => $registrations->get($week, 0),
                'votes' => $votes->get($week, 0),
                'user_battles' => $userBattles->get($week, 0),
                'paired_battles' => $pairedBattles->get($week, 0),
                'creations' => $tracks->get($week, 0) + $designs->get($week, 0),
            ];
        }, range(0, $weeks - 1));
    }

    /**
     * How many records were created each week since the given date, keyed by the Monday of the week.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Collection<string, int>
     */
    private function perWeek(Builder $query, Carbon $since): Collection
    {
        return $query->where('created_at', '>=', $since)
            ->pluck('created_at')
            ->countBy(fn (Carbon $createdAt) => $createdAt->startOfWeek()->toDateString());
    }
}
