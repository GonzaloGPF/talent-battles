<?php

namespace Tests\Feature\Reports;

use App\Enums\Roles;
use App\Models\Report;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        create(Report::class, [], 3);
    }

    #[Test]
    public function it_let_fetch_all_reports()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(3, $data);
    }

    #[Test]
    public function it_let_view_an_specific_report()
    {
        /** @var Report $report */
        $report = create(Report::class);

        $data = $this->getJson(route('reports.show', $report))
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($report->id, $data['id']);
    }

    #[Test]
    public function it_only_let_user_view_report_created_by_him()
    {
        /** @var User $user */
        $user = create(User::class, ['role' => Roles::USER]);

        create(Report::class, [
            'created_by' => $user->id,
            'reportable_type' => null,
            'reportable_id' => null,
        ]);

        $this->signIn($user);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_also_let_user_view_report_if_reportable_have_been_created_by_him()
    {
        /** @var User $user */
        $user = create(User::class, ['role' => Roles::USER]);
        /** @var User $otherUser */
        $otherUser = create(User::class, ['role' => Roles::USER]);

        /** @var Track $track */
        $track = create(Track::class, ['created_by' => $user->id]);

        create(Report::class, [
            'created_by' => $otherUser->id,
            'reportable_type' => Track::class,
            'reportable_id' => $track->id,
        ]);

        $this->signIn($user);

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('reports.index'));
    }
}
