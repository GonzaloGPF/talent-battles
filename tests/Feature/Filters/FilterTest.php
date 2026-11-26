<?php

namespace Tests\Feature\Filters;

use App\Models\Like;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class FilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    #[Test]
    public function it_can_filter_a_polymorph_model_by_model_type()
    {
        create(Like::class, [
            'likeable_id' => 1,
            'likeable_type' => 'App\Models\OtherType',
        ]); // Extra likes

        create(Like::class, [
            'likeable_id' => 2,
            'likeable_type' => 'App\Models\TestType',
        ]);

        $data = $this->makeRequest(route('likes.index', [
            'model_type' => 'test_type',
        ]));

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_a_polymorph_model_by_model_id()
    {
        create(Like::class, [
            'likeable_id' => 1,
            'likeable_type' => 'App\Models\OtherType',
        ]); // Extra likes

        create(Like::class, [
            'likeable_id' => 2,
            'likeable_type' => 'App\Models\TestType',
        ]);

        $data = $this->makeRequest(route('likes.index', [
            'model_id' => 1,
        ]));

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_let_count_relationships()
    {
        /** @var Track $creation */
        $creation = create(Track::class);

        create(Like::class, [
            'created_by' => create(User::class)->id,
            'likeable_type' => Track::class,
            'likeable_id' => $creation->id,
        ]);

        create(Like::class, [
            'created_by' => create(User::class)->id,
            'likeable_type' => Track::class,
            'likeable_id' => $creation->id,
        ]);

        $data = $this->makeRequest(route('tracks.index', [
            'counts' => ['likes'],
        ]));

        $this->assertEquals(2, $data[0]['likes_count']);
    }

    #[Test]
    public function it_let_count_nested_relationships()
    {
        /** @var User $user */
        $user = create(User::class);
        /** @var Track $creation */
        $creation = create(Track::class, ['created_by' => $user->id]);

        create(Like::class, [
            'created_by' => create(User::class)->id,
            'likeable_type' => User::class,
            'likeable_id' => $user->id,
        ]);

        create(Like::class, [
            'created_by' => create(User::class)->id,
            'likeable_type' => User::class,
            'likeable_id' => $user->id,
        ]);

        $data = $this->makeRequest(route('tracks.index', [
            'counts' => ['createdBy.likes'],
        ]));

        $this->assertEquals(2, $data[0]['createdBy']['likes_count']);
    }

    #[Test]
    public function it_can_filter_limiting_results()
    {
        create(Track::class, [], 3);

        $data = $this->makeRequest(route('tracks.index', [
            'limit' => 2,
        ]));

        $this->assertCount(2, $data);
    }

    #[Test]
    public function it_can_filter_by_ranged_values()
    {
        Product::query()->update(['price' => 0]);

        create(Product::class, ['price' => 10]);
        create(Product::class, ['price' => 20]);
        create(Product::class, ['price' => 30]);
        create(Product::class, ['price' => 40]);

        $data = $this->makeRequest(route('products.index', [
            'min_price' => 15,
            'max_price' => 32,
        ]));

        $this->assertCount(2, $data);
    }

    #[Test]
    public function it_can_filter_by_ranged_dates()
    {
        create(Track::class, ['created_at' => today()->subDays(2)]);
        $creation = create(Track::class, ['created_at' => today()->subDay()]);
        create(Track::class, ['created_at' => today()]);
        create(Track::class, ['created_at' => today()->addDay()]);

        $data = $this->makeRequest(route('tracks.index', [
            'min_created_at' => today()->subDay()->toDateTimeString(),
            'max_created_at' => today()->toDateTimeString(),
        ]));

        $this->assertCount(2, $data);
    }

    #[Test]
    public function it_can_filter_by_not_attributes()
    {
        create(User::class, ['name' => 'User 1']);
        create(User::class, ['name' => 'User 2']);
        create(User::class, ['name' => 'User 3']);

        $data = $this->makeRequest(route('users.index', [
            'not_name' => ['User 1'],
        ]));

        $this->assertCount(2, $data);
    }

    #[Test]
    #[TestWith(['where_in'])]
    #[TestWith(['boolean'])]
    #[TestWith(['nullable'])]
    #[TestWith(['filter_multiple'])]
    #[TestWith(['apply_relations'])]
    #[TestWith(['can_use_column'])]
    #[TestWith(['setup'])]
    public function an_input_named_after_a_helper_of_the_filter_is_ignored(string $input)
    {
        create(User::class, ['name' => 'User 1']);
        create(User::class, ['name' => 'User 2']);

        $data = $this->makeRequest(route('users.index', [$input => 'x']));

        $this->assertCount(2, $data);
    }

    #[Test]
    #[TestWith(['name|asc', ['Ana', 'Bea', 'Carla']])]
    #[TestWith(['name|DESC', ['Carla', 'Bea', 'Ana']])]
    #[TestWith(['name|sideways', ['Ana', 'Bea', 'Carla']])]
    public function results_are_sorted_in_the_given_direction_and_ascending_for_an_unknown_one(string $sort, array $names)
    {
        $users = collect(['Bea', 'Carla', 'Ana'])->map(fn (string $name) => create(User::class, ['name' => $name]));

        $data = $this->makeRequest(route('users.index', ['ids' => $users->pluck('id')->all(), 'sort' => $sort]));

        $this->assertSame($names, array_column($data, 'name'));
    }

    private function makeRequest(string $route): array
    {
        return $this->getJson($route)
            ->assertSuccessful()
            ->json('data');
    }
}
