<?php

namespace Tests\Feature\Helpers;

use App\Models\Battle;
use App\Models\Like;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RelationHelperTest extends TestCase
{
    use RefreshDatabase;

    public static bool $constraintWasCalled = false;

    #[Test]
    #[TestWith(['with', 'forceDelete'])]
    #[TestWith(['with', 'delete'])]
    #[TestWith(['with', 'truncate'])]
    #[TestWith(['with', 'unknown'])]
    #[TestWith(['counts', 'forceDelete'])]
    #[TestWith(['counts', 'delete'])]
    #[TestWith(['counts', 'unknown'])]
    public function a_name_that_is_not_a_relation_is_ignored_when_showing_a_record(string $input, string $name)
    {
        $track = create(Track::class);

        $this->showTrack($track, [$input => [$name]])
            ->assertOk();

        $this->assertModelExists($track);
    }

    #[Test]
    #[TestWith(['with', 'forceDelete'])]
    #[TestWith(['with', 'truncate'])]
    #[TestWith(['with', 'unknown'])]
    #[TestWith(['counts', 'forceDelete'])]
    #[TestWith(['counts', 'unknown'])]
    #[TestWith(['counts', 'createdBy.forceDelete'])]
    public function a_name_that_is_not_a_relation_is_ignored_when_listing_records(string $input, string $name)
    {
        $tracks = create(Track::class, [], 2);

        $this->getJson(route('tracks.index', [$input => [$name]]))
            ->assertOk();

        $this->assertSame(2, Track::query()->whereKey($tracks->modelKeys())->count());
    }

    #[Test]
    public function the_value_of_a_keyed_relation_is_never_called_when_showing_a_record()
    {
        self::$constraintWasCalled = false;
        $track = create(Track::class);

        $this->showTrack($track, $this->keyedRelations())
            ->assertOk();

        $this->assertFalse(self::$constraintWasCalled);
    }

    #[Test]
    public function the_value_of_a_keyed_relation_is_never_called_when_listing_records()
    {
        self::$constraintWasCalled = false;
        create(Track::class);

        $this->getJson(route('tracks.index', $this->keyedRelations()))
            ->assertOk();

        $this->assertFalse(self::$constraintWasCalled);
    }

    #[Test]
    public function the_requested_relations_and_counts_of_a_record_are_loaded()
    {
        $track = create(Track::class);
        create(Like::class, [
            'created_by' => create(User::class)->id,
            'likeable_type' => Track::class,
            'likeable_id' => $track->id,
        ]);

        $this->showTrack($track, ['with' => ['created_by'], 'counts' => ['likes']])
            ->assertOk()
            ->assertJsonPath('data.createdBy.id', $track->created_by)
            ->assertJsonPath('data.likes_count', 1);
    }

    #[Test]
    public function a_relation_reached_through_a_morph_to_relation_is_loaded()
    {
        $track = create(Track::class);
        create(Battle::class, ['winner_type' => Track::class, 'winner_id' => $track->id]);

        $this->getJson(route('battles.index', ['with' => ['winner.createdBy']]))
            ->assertOk()
            ->assertJsonPath('data.0.winner.createdBy.id', $track->created_by);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function showTrack(Track $track, array $input): TestResponse
    {
        return $this->getJson(route('tracks.show', ['track' => $track->id, ...$input]));
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function keyedRelations(): array
    {
        $constraint = __NAMESPACE__.'\\markConstraintAsCalled';

        return ['with' => ['createdBy' => $constraint], 'counts' => ['likes' => $constraint]];
    }
}

/**
 * Stands for any function a request could name as the value of a keyed relation.
 */
function markConstraintAsCalled(): void
{
    RelationHelperTest::$constraintWasCalled = true;
}
