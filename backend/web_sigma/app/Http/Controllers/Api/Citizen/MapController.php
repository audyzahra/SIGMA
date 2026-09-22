<?php

namespace App\Http\Controllers\Api\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Resources\Citizen\IncidentResource;
use App\Http\Resources\Citizen\ReportResource;
use App\Models\FireRisk;
use App\Models\Hotspot;
use App\Models\Incident;
use App\Services\Citizen\CitizenReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __construct(private readonly CitizenReportService $reports) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'hotspots' => Hotspot::query()->latest('detected_at')->get()->map(fn (Hotspot $hotspot): array => [
                'id' => $hotspot->id,
                'latitude' => (float) $hotspot->latitude,
                'longitude' => (float) $hotspot->longitude,
                'status' => $hotspot->status,
                'confidence_level' => $hotspot->confidence_level,
                'detected_at' => $hotspot->detected_at?->toIso8601String(),
            ])->values(),
            'incidents' => IncidentResource::collection(Incident::query()->latest('detected_at')->get())->resolve(),
            'reports' => ReportResource::collection($this->reports->forUser($request->user()))->resolve(),
            'risk' => $this->latestRisk(),
        ]);
    }

    /** @return array<string, mixed>|null */
    private function latestRisk(): ?array
    {
        $risk = FireRisk::query()->with('region')->latest('calculated_at')->first();

        return $risk ? [
            'score' => $risk->risk_score,
            'level' => $risk->risk_level,
            'region' => $risk->region?->name,
            'calculated_at' => $risk->calculated_at?->toIso8601String(),
        ] : null;
    }
}
