<?php

namespace App\Models;

use App\Enums\ReportStates;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasFilesInterface;
use App\Scopes\ReportScope;
use App\Traits\HasCreatedBy;
use App\Traits\HasFiles;
use App\Traits\IsFilterable;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperReport
 */
class Report extends Model implements HasCreatedByInterface, HasFilesInterface
{
    /** @use HasFactory<ReportFactory> */
    use HasCreatedBy,
        HasFactory,
        HasFiles,
        IsFilterable;

    protected $casts = [
        'report_state' => ReportStates::class,
        'reportable_id' => 'int',
        'closed_at' => 'datetime',
    ];

    protected $fillable = [
        'message',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ReportScope);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isFinished(): bool
    {
        return in_array($this->report_state->value, ReportStates::getFinishedStates());
    }

    public function getReportedUser(): ?User
    {
        return $this->reportable?->getRelationValue('createdBy');
    }
}
