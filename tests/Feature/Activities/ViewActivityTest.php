<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        create(Activity::class, [
            'target_id' => 0,
            'target_type' => 'SomeType',
        ]);
    }

    #[Test]
    public function it_can_view_activities()
    {
        $this->makeRequest()
            ->assertSuccessful();
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('activities.index'));
    }
}
