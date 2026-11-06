<?php

namespace Tests\Feature\Filters;

use App\Enums\ReportStates;
use App\Models\Report;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        create(Report::class, [], 3);
    }

    #[Test]
    public function it_filter_reports_by_message()
    {
        create(Report::class, ['message' => 'Test']);

        $data = Report::filter(['message' => 'Test'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_filter_reports_by_resolution()
    {
        create(Report::class, ['resolution' => 'Test']);

        $data = Report::filter(['resolution' => 'Test'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_filter_reports_by_report_state()
    {
        Report::query()->update(['report_state' => ReportStates::OPEN]);

        create(Report::class, ['report_state' => ReportStates::SOLVED]);

        $data = Report::filter(['report_states' => [ReportStates::SOLVED->value]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_filter_reports_by_solved()
    {
        Report::query()->update(['resolution' => null]);

        create(Report::class, ['resolution' => 'This is a resolution']);

        $data = Report::filter(['solved' => true])->get();

        $this->assertCount(1, $data);

        $data = Report::filter(['solved' => false])->get();

        $this->assertCount(3, $data);
    }

    #[Test]
    public function it_can_filter_reports_by_has_creation()
    {
        Report::query()->update([
            'reportable_id' => null,
            'reportable_type' => null,
        ]);

        create(Report::class, [
            'reportable_id' => create(Track::class)->id,
            'reportable_type' => Track::class,
        ]);

        $data = Report::filter(['has_creation' => true])->get();

        $this->assertCount(1, $data);

        $data = Report::filter(['has_creation' => false])->get();

        $this->assertCount(3, $data);
    }
}
