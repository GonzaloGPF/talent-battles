<?php

namespace Tests\Feature\Services;

use App\Enums\ReportStates;
use App\Models\Report;
use App\Models\Track;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->service = resolve(ReportService::class);
    }

    #[Test]
    public function it_can_create_a_report()
    {
        $report = $this->service->create('Some Message');

        tap($report->fresh(), function (Report $report) {
            $this->assertEquals('Some Message', $report->message);
            $this->assertEquals(ReportStates::OPEN, $report->report_state);
        });
    }

    #[Test]
    public function when_creating_a_report_it_can_specify_a_model_to_report()
    {
        /** @var Track $track */
        $track = create(Track::class);

        $report = $this->service->create('Some Message', $track);

        tap($report->fresh(), function (Report $report) {
            $this->assertEquals('Some Message', $report->message);
            $this->assertInstanceOf(Track::class, $report->reportable);
            $this->assertEquals(ReportStates::OPEN, $report->report_state);
        });
    }

    #[Test]
    public function it_can_mark_as_finished_a_report()
    {
        /** @var Report $report */
        $report = create(Report::class, [
            'report_state' => ReportStates::OPEN,
            'closed_at' => null,
        ]);

        $report = $this->service->finish($report, 'Some resolution');

        $this->assertEquals(ReportStates::SOLVED, $report->report_state);
        $this->assertEquals('Some resolution', $report->resolution);
        $this->assertNotNull($report->closed_at);
    }

    #[Test]
    public function when_marking_as_finished_a_report_it_can_specify_it_as_closed()
    {
        /** @var Report $report */
        $report = create(Report::class, [
            'report_state' => ReportStates::OPEN,
            'closed_at' => null,
        ]);

        $report = $this->service->finish($report, 'Some resolution', true);

        $this->assertEquals(ReportStates::CLOSED, $report->report_state);
        $this->assertEquals('Some resolution', $report->resolution);
        $this->assertNotNull($report->closed_at);
    }
}
