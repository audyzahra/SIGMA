<?php

namespace App\Http\Controllers\Api\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\StoreFieldReportRequest;
use App\Models\FieldAssignment;
use App\Models\FieldVerification;
use App\Services\Officer\OfficerTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly OfficerTaskService $tasks) {}

    public function index(Request $request): JsonResponse
    {
        $teamIds = $request->user()->fieldTeams()->select('field_teams.id');

        return response()->json(['reports' => FieldVerification::query()->whereIn('team_id', $teamIds)->with('incident')->latest('verified_at')->get()->map(fn ($report): array => $this->present($report))->values()]);
    }

    public function store(StoreFieldReportRequest $request, FieldAssignment $task): JsonResponse
    {
        abort_unless($this->tasks->belongsToUser($request->user(), $task), 404);
        $report = FieldVerification::create(['incident_id' => $task->incident_id, 'team_id' => $task->team_id, 'description' => $request->string('description')->toString(), 'verified_at' => now()]);

        return response()->json(['report' => $this->present($report->load('incident'))], 201);
    }

    private function present(FieldVerification $report): array
    {
        return ['id' => $report->id, 'description' => $report->description, 'verified_at' => $report->verified_at?->toIso8601String(), 'incident' => ['id' => $report->incident->id, 'location_description' => $report->incident->location_description, 'fire_status' => $report->incident->fire_status]];
    }
}
