<?php

namespace App\Http\Controllers\Api\Officer;

use App\Http\Controllers\Controller;
use App\Models\FireRisk;
use App\Services\Officer\OfficerTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly OfficerTaskService $tasks) {}

    public function __invoke(Request $request): JsonResponse
    {
        $tasks = $this->tasks->forUser($request->user());
        $priorityTask = $tasks->sortByDesc(fn ($task): int => match ($task->priority_level) {
            'extreme' => 4, 'high' => 3, 'medium' => 2, default => 1,
        })->first();
        $risk = FireRisk::query()->with('region')->latest('calculated_at')->first();

        return response()->json([
            'profile' => ['name' => $request->user()->name, 'email' => $request->user()->email],
            'statistics' => ['active_tasks' => $tasks->where('status', '!=', 'completed')->count(), 'active_incidents' => $tasks->where('status', '!=', 'completed')->pluck('incident_id')->unique()->count()],
            'priority_task' => $priorityTask ? $this->tasks->present($priorityTask) : null,
            'risk' => $risk ? ['score' => $risk->risk_score, 'level' => $risk->risk_level, 'region' => $risk->region?->name] : null,
            'unread_notification_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
