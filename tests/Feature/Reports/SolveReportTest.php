<?php

namespace Tests\Feature\Reports;

use App\Enums\ReportStates;
use App\Enums\Roles;
use App\Events\ReportClosed;
use App\Models\Report;
use App\Models\Track;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SolveReportTest extends TestCase
{
    use RefreshDatabase;

    private Report $report;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();

        $this->user = $this->signIn();

        /** @var User $someUser */
        $someUser = create(User::class);
        /** @var Track $track */
        $track = create(Track::class, ['created_by' => $someUser->id]);
        /** @var User $reporterUser */
        $reporterUser = create(User::class);

        $this->report = create(Report::class, [
            'created_by' => $reporterUser->id,
            'reportable_type' => Track::class,
            'reportable_id' => $track->id,
            'report_state' => ReportStates::OPEN,
            'closed_at' => null,
        ]);
    }

    #[Test]
    public function it_let_admin_user_solve_reports_in_open_state()
    {
        $this->makeRequest()
            ->assertSuccessful();

        Event::assertDispatched(ReportClosed::class);

        tap($this->report->fresh(), function (Report $report) {
            $this->assertNotNull($report->closed_at);
            $this->assertEquals(ReportStates::SOLVED, $report->report_state);
            $this->assertEquals('Some resolution text', $report->resolution);
        });
    }

    #[Test]
    public function it_let_admin_user_solve_reports_in_working()
    {
        $this->report
            ->forceFill(['report_state' => ReportStates::WORKING])
            ->saveQuietly();

        $this->makeRequest()
            ->assertSuccessful();

        Event::assertDispatched(ReportClosed::class);

        tap($this->report->fresh(), function (Report $report) {
            $this->assertNotNull($report->closed_at);
            $this->assertEquals(ReportStates::SOLVED, $report->report_state);
            $this->assertEquals('Some resolution text', $report->resolution);
        });
    }

    #[Test]
    public function when_solving_a_report_admin_can_close_instead()
    {
        $this->makeRequest(['closed' => true])
            ->assertSuccessful();

        Event::assertDispatched(ReportClosed::class);

        tap($this->report->fresh(), function (Report $report) {
            $this->assertNotNull($report->closed_at);
            $this->assertEquals(ReportStates::CLOSED, $report->report_state);
            $this->assertEquals('Some resolution text', $report->resolution);
        });
    }

    #[Test]
    public function it_wont_let_solve_a_report_if_it_has_solved_state()
    {
        $this->report
            ->forceFill(['report_state' => ReportStates::SOLVED])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_solve_a_report_if_it_has_closed_state()
    {
        $this->report
            ->forceFill(['report_state' => ReportStates::CLOSED])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_solve_a_report_to_non_admin_users()
    {
        $this->user
            ->forceFill(['role' => Roles::USER])
            ->saveQuietly();

        $this->makeRequest()
            ->assertNotFound();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('reports.solve', $this->report), array_merge([
            'resolution' => 'Some resolution text',
            'closed' => false,
        ], $attributes));
    }
}
