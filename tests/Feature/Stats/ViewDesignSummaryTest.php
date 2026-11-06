<?php

namespace Tests\Feature\Stats;

use App\Enums\DesignTypes;
use App\Models\Design;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewDesignSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
    }

    #[Test]
    public function it_let_view_design_summary_stats_data()
    {
        create(Design::class, ['design_type' => DesignTypes::LOGO]);
        create(Design::class, ['design_type' => DesignTypes::LOGO]);
        create(Design::class, ['design_type' => DesignTypes::FREE]);
        create(Design::class, [ // Should be ignored
            'design_type' => DesignTypes::LOGO,
            'created_by' => create(User::class)->id,
        ]);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals(modelTitle('design', true), $data['datasets'][0]['label']);
        $this->assertEquals([1, 2], $data['datasets'][0]['data']);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('stats.designs.summary'));
    }
}
