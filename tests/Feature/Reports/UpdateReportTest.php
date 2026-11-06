<?php

namespace Tests\Feature\Reports;

use App\Enums\ReportStates;
use App\Enums\Roles;
use App\Models\Report;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Report $report;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = create(User::class, ['role' => Roles::USER]);

        /** @var Track $track */
        $track = create(Track::class);

        $this->report = create(Report::class, [
            'created_by' => $this->user->id,
            'reportable_id' => $track->id,
            'reportable_type' => Track::class,
            'report_state' => ReportStates::OPEN,
            'resolution' => null,
            'closed_at' => null,
        ]);

        $this->signIn($this->user);
    }

    #[Test]
    public function it_let_update_an_open_report()
    {
        $this->makeRequest()
            ->assertSuccessful();

        tap($this->report->fresh(), function (Report $report) {
            $this->assertEquals(ReportStates::OPEN, $report->report_state);
            $this->assertEquals('Edited', $report->message);
            $this->assertNull($report->closed_at);
        });
    }

    #[Test]
    public function it_wont_let_remove_the_message()
    {
        $this->makeRequest(['message' => null])
            ->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_update_a_report_if_its_not_open()
    {
        $this->report
            ->forceFill(['report_state' => ReportStates::WORKING])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();

        $this->report
            ->forceFill(['report_state' => ReportStates::SOLVED])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();

        $this->report
            ->forceFill(['report_state' => ReportStates::CLOSED])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_update_a_report_if_its_not_created_by_user()
    {
        $this->report
            ->forceFill(['created_by' => create(User::class)->id])
            ->saveQuietly();

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('reports.update', $this->report), array_merge(['message' => 'Edited'], $attributes));
    }
}
