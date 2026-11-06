<?php

namespace Database\Seeders;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\TrackTypes;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use UnitEnum;

class BattleSeeder extends Seeder
{
    private Collection $users;

    private Collection $tracks;

    private Collection $designs;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->users = User::all();
        $this->tracks = Track::all();
        $this->designs = Design::all();

        Battle::factory()
            ->count(5)
            ->create([
                'created_by' => $this->users->random()->id,
                'battle_state' => BattleStates::IN_PROGRESS,
                'battle_type' => BattleTypes::TRACK,
            ])->each(fn (Battle $battle) => $this->setTrackBattle($battle, TrackTypes::BEAT));

        Battle::factory()
            ->count(5)
            ->create([
                'created_by' => $this->users->random()->id,
                'battle_state' => BattleStates::IN_PROGRESS,
                'battle_type' => BattleTypes::TRACK,
            ])->each(fn (Battle $battle) => $this->setTrackBattle($battle, TrackTypes::SONG));

        Battle::factory()
            ->count(5)
            ->create([
                'created_by' => $this->users->random()->id,
                'battle_state' => BattleStates::IN_PROGRESS,
                'battle_type' => BattleTypes::TRACK,
            ])->each(fn (Battle $battle) => $this->setTrackBattle($battle, TrackTypes::SAMPLE));

        Battle::factory()
            ->count(5)
            ->create([
                'created_by' => $this->users->random()->id,
                'battle_state' => BattleStates::IN_PROGRESS,
                'battle_type' => BattleTypes::DESIGN,
            ])->each(fn (Battle $battle) => $this->setDesignBattle($battle));
    }

    private function setTrackBattle(Battle $battle, UnitEnum $trackType): Battle
    {
        $tracks = $this->tracks
            ->where('track_type', $trackType)
            ->unique('created_by')
            ->random(2);

        $battle->tracks()->saveMany($tracks);

        if (rand(0, 1)) {
            $battle->forceFill([
                'battle_state' => BattleStates::FINISHED,
                'winner_type' => Track::class,
                'winner_id' => $tracks->random()->id,
            ])->saveQuietly();
        }

        return $battle;
    }

    private function setDesignBattle(Battle $battle): Battle
    {
        $designs = $this->designs
            ->unique('created_by')
            ->random(2);

        $battle->designs()->saveMany($designs);

        if (rand(0, 1)) {
            $battle->forceFill([
                'battle_state' => BattleStates::FINISHED,
                'winner_type' => Design::class,
                'winner_id' => $designs->random()->id,
            ])->saveQuietly();
        }

        return $battle;
    }
}
