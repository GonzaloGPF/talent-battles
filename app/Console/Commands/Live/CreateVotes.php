<?php

namespace App\Console\Commands\Live;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use App\Services\VoteService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CreateVotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-votes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create random votes for battles that are in progress';

    /**
     * Execute the console command.
     */
    public function handle(VoteService $voteService): void
    {
        $users = User::select(['id', 'level'])
            ->with([
                'tracks' => fn (HasMany $q) => $q->select(['id', 'created_by']),
                'designs' => fn (HasMany $q) => $q->select(['id', 'created_by']),
            ])
            ->get();

        $battles = Battle::with([
            'tracks' => fn (BelongsToMany $q) => $q->select(['id']),
            'designs' => fn (BelongsToMany $q) => $q->select(['id']),
        ])
            ->where('battle_state', BattleStates::IN_PROGRESS)
            ->get();

        $this->info("Found {$battles->count()} Battles");

        $total = $battles
            ->flatMap(fn (Battle $battle) => $this->createVotes($voteService, $battle, $users))
            ->filter()
            ->count();

        if (! $total) {
            $this->info('No votes has been created');
        } else {
            $this->info("Created $total Votes!\n");
        }
    }

    private function createVotes(VoteService $voteService, Battle $battle, Collection $users): Collection
    {
        return collect(range(0, rand(1, 10)))
            ->map(function () use ($voteService, $battle, $users) {
                if ($battle->battle_type === BattleTypes::TRACK) {
                    return $this->generateTrackVote($voteService, $battle, $users);
                }
                if ($battle->battle_type === BattleTypes::DESIGN) {
                    return $this->generateDesignVote($voteService, $battle, $users);
                }

                return null;
            })
            ->filter();
    }

    private function generateTrackVote(VoteService $voteService, Battle $battle, Collection $users): ?Model
    {
        /** @var Track $track */
        $track = $battle->tracks->random();

        /** @var User $user */
        $user = $users
            ->filter(fn (User $user) => ! in_array($track->id, $user->tracks->modelKeys()))
            ->random();

        if (! $user) {
            return null;
        }

        return $voteService->create($battle->id, $track, $user);
    }

    private function generateDesignVote(VoteService $voteService, Battle $battle, Collection $users): ?Model
    {
        /** @var Design $design */
        $design = $battle->designs->random();

        /** @var User $user */
        $user = $users
            ->filter(fn (User $user) => ! in_array($design->id, $user->designs->modelKeys()))
            ->random();

        if (! $user) {
            return null;
        }

        return $voteService->create($battle->id, $design, $user);
    }
}
