<?php

namespace App\Http\Resources\Citizen;

use App\Models\CitizenReport;
use App\Models\ReportStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var CitizenReport $report */
        $report = $this->resource;
        $latestHistory = $report->statusHistories->sortByDesc('created_at')->first();
        $status = $latestHistory?->status ?? match ($report->verification_status) {
            'verified' => 'verified', 'rejected' => 'rejected', default => 'submitted',
        };

        return [
            'id' => $report->id,
            'number' => 'RPT-'.str_pad((string) $report->id, 6, '0', STR_PAD_LEFT),
            'report_type' => $report->report_type,
            'latitude' => (float) $report->latitude,
            'longitude' => (float) $report->longitude,
            'description' => $report->description,
            'photo_url' => $report->photo ? url('storage/'.$report->photo) : null,
            'verification_status' => $report->verification_status,
            'status' => $status,
            'created_at' => $report->created_at?->toIso8601String(),
            'updated_at' => $report->updated_at?->toIso8601String(),
            'incident' => $this->whenLoaded('incident', fn () => $report->incident ? new IncidentResource($report->incident) : null),
            'history' => $this->when($report->relationLoaded('statusHistories'), fn () => $report->statusHistories->sortBy('created_at')->values()->map(fn (ReportStatusHistory $history): array => [
                'status' => $history->status,
                'description' => $history->description,
                'updated_at' => $history->created_at?->toIso8601String(),
            ])),
        ];
    }
}
