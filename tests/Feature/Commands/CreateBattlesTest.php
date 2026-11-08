<?php

namespace Tests\Feature\Commands;

use App\Enums\TrackTypes;
use App\Models\Battle;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateBattlesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_fills_the_battles_in_progress_and_says_how_many_it_created()
    {
        config(['company.battles_in_progress' => 1]);
        create(Track::class, ['created_by' => create(User::class)->id, 'track_type' => TrackTypes::BEAT, 'can_challenge' => true]);
        create(Track::class, ['created_by' => create(User::class)->id, 'track_type' => TrackTypes::BEAT, 'can_challenge' => true]);

        $this->artisan('app:create-battles')
            ->expectsOutput('Created 1 Battles!')
            ->assertSuccessful();

        $this->assertEquals(1, Battle::areInProgress()->count());
    }
}
