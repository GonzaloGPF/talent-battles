<?php

namespace App\Services;

use App\Enums\ReportStates;
use App\Models\Report;
use Illuminate\Database\Eloquent\Model;

class ReportService
{
    public function create(string $message, ?Model $model = null): Report
    {
        $report = new Report;
        $report->message = $message;

        if ($model) {
            $report->reportable()->associate($model);
        }

        $report->save();

        return $report;
    }

    public function finish(Report $report, string $resolution, ?bool $closed = false): Report
    {
        $report->forceFill([
            'report_state' => $closed
                ? ReportStates::CLOSED
                : ReportStates::SOLVED,
            'resolution' => $resolution,
            'closed_at' => now(),
        ])->save();

        return $report;
    }
}
