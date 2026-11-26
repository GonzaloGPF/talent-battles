<?php

namespace Tests\Feature\Traits;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class IsFilterableTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_page_size_within_range_is_used()
    {
        create(User::class, [], 6);

        $this->getJson(route('users.index', ['per_page' => 5]))
            ->assertOk()
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    #[TestWith(['-3'])]
    #[TestWith(['0'])]
    #[TestWith(['abc'])]
    #[TestWith(['1001'])]
    public function a_page_size_out_of_range_gives_pages_of_15(string $perPage)
    {
        create(User::class, [], 16);

        $this->getJson(route('users.index', ['per_page' => $perPage]))
            ->assertOk()
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonCount(15, 'data');
    }

    #[Test]
    #[TestWith(['-1'])]
    #[TestWith(['0'])]
    #[TestWith(['abc'])]
    #[TestWith(['1001'])]
    public function a_limit_out_of_range_gives_the_first_page_instead(string $limit)
    {
        create(User::class, [], 16);

        $this->getJson(route('users.index', ['limit' => $limit]))
            ->assertOk()
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonCount(15, 'data');
    }

    #[Test]
    public function a_page_that_is_not_a_number_gives_the_first_page()
    {
        create(User::class, [], 2);

        $this->getJson(route('users.index', ['page' => 'abc']))
            ->assertOk()
            ->assertJsonPath('meta.current_page', 1);
    }

    #[Test]
    public function the_request_cannot_choose_the_selected_columns()
    {
        create(User::class, ['name' => 'Visible Name']);

        $response = $this->getJson(route('users.index', ['columns' => ['id']]));

        $response->assertOk();
        $this->assertContains('Visible Name', $response->json('data.*.name'));
    }
}
