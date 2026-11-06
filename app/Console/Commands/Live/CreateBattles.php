<?php

namespace App\Console\Commands\Live;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CreateBattles extends Command
{
    private Collection $tracks;

    private Collection $opponents;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-battles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create some random battles';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->opponents = collect();

        $users = User::select(['id'])
            ->whereHas('beats')
            ->with([
                'beats' => fn (HasMany $q) => $q->select(['id', 'created_by', 'track_type']),
            ])
            ->get();

        $this->tracks = $users->flatMap(fn (User $user) => $user->beats);

        $this->info("Found {$this->tracks->count()} Tracks");

        $total = $this->tracks
            ->shuffle()
            ->filter(fn () => rand(1, 10) > 7)
            ->map(fn (Track $track) => $this->createTrackBattle($track))
            ->filter()
            ->count();

        $this->info("Created $total Battles!\n");
    }

    private function createTrackBattle(Track $track): ?Battle
    {
        /** @var Track $opponent */
        $opponent = $this->tracks
            ->filter(fn (Track $opponentTrack) => ! $this->opponents->contains($opponentTrack))
            ->where(fn (Track $opponentTrack) => $opponentTrack->isNot($track) && $track->created_by !== $opponentTrack->created_by)
            ->random();

        if (! $opponent) {
            return null;
        }

        $this->opponents->push($opponent);

        try {
            $battle = Battle::forceCreate([
                'created_by' => $track->created_by,
                'battle_state' => BattleStates::IN_PROGRESS,
                'battle_type' => BattleTypes::TRACK,
            ]);

            $battle->tracks()->saveMany([$track, $opponent]);

            return $battle;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return null;
        }
    }
}
