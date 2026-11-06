<?php

namespace Tests\Feature\Filters;

use App\Models\FavoriteList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteListFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->user = $this->signIn();

        // Extra favoriteLists
        create(FavoriteList::class, [
            'created_by' => $this->user->id,
            'name' => 'SomeName',
        ]);
    }

    #[Test]
    public function it_can_filter_users_by_name()
    {
        create(FavoriteList::class, ['name' => 'test']);

        $data = FavoriteList::filter(['name' => 'test'])->get();

        $this->assertCount(1, $data);
    }
}
