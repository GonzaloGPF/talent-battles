<?php

namespace App\Http\Resources;

use App\Models\Report;
use Illuminate\Http\Request;

/**
 * @mixin Report
 */
class ReportResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'report_state' => $this->report_state,
            'message' => $this->message,
            'reportable_id' => $this->reportable_id,
            'reportable_type' => class_basename($this->reportable_type),
            'resolution' => $this->resolution,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'file' => new FileResource($this->whenLoaded('file')),
            'reportable' => $this->morphedResource('reportable'),
        ];
    }
}
