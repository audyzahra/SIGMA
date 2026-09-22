<?php

namespace App\Http\Controllers\Api\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Resources\Citizen\IncidentResource;
use App\Http\Resources\Citizen\ProfileResource;
use App\Http\Resources\Citizen\ReportResource;
use App\Models\FireRisk;
use App\Models\Incident;
use App\Models\SystemConfiguration;
use App\Services\Citizen\CitizenReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly CitizenReportService $reports) {}

    public function __invoke(Request $request): JsonResponse
    {
        $reports = $this->reports->forUser($request->user());
        $risk = FireRisk::query()->with('region')->latest('calculated_at')->first();
        $number = SystemConfiguration::query()
            ->whereIn('key', ['emergency_number', 'emergency_contact'])
            ->value('value');

        return response()->json([
            'profile' => (new ProfileResource($request->user()))->resolve(),
            'report_statistics' => $this->reports->statistics($reports),
            'latest_reports' => ReportResource::collection($reports->take(3))->resolve(),
            'risk' => $risk ? [
                'score' => $risk->risk_score,
                'level' => $risk->risk_level,
                'region' => $risk->region?->name,
                'calculated_at' => $risk->calculated_at?->toIso8601String(),
            ] : null,
            'incidents' => IncidentResource::collection(Incident::query()
                ->whereIn('fire_status', ['detected', 'on_process'])
                ->latest('detected_at')->take(3)->get())->resolve(),
            'emergency' => ['number' => $number ?: '112', 'configured' => (bool) $number],
            'notifications_available' => true,
            'unread_notification_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
