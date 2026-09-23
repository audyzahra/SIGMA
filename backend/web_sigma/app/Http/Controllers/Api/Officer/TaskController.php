<?php

namespace App\Http\Controllers\Api\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\UpdateTaskStatusRequest;
use App\Models\FieldAssignment;
use App\Models\IncidentStatusHistory;
use App\Notifications\OfficerTaskUpdated;
use App\Services\Officer\OfficerTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private readonly OfficerTaskService $tasks) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['tasks' => $this->tasks->forUser($request->user())->map(fn ($task): array => $this->tasks->present($task))->values()]);
    }

    public function show(Request $request, FieldAssignment $task): JsonResponse
    {
        abort_unless($this->tasks->belongsToUser($request->user(), $task), 404);
        $task->load(['incident.statusHistories', 'team.organization', 'acceptor']);

        return response()->json(['task' => $this->tasks->present($task)]);
    }

    public function accept(Request $request, FieldAssignment $task): JsonResponse
    {
        abort_unless($this->tasks->belongsToUser($request->user(), $task), 404);
        $wasAccepted = $task->accepted_at !== null;
        if (! $wasAccepted) {
            $task->update(['accepted_by' => $request->user()->id, 'accepted_at' => now()]);
        }
        if (! $wasAccepted && $task->assigner) {
            $task->assigner->notify(new OfficerTaskUpdated($task, 'accepted'));
        }
        $task->load(['incident.statusHistories', 'team.organization', 'acceptor']);

        return response()->json(['task' => $this->tasks->present($task)]);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, FieldAssignment $task): JsonResponse
    {
        abort_unless($this->tasks->belongsToUser($request->user(), $task), 404);
        abort_unless($task->accepted_at, 422, 'Tugas harus diterima terlebih dahulu.');
        $status = $request->string('status')->toString();
        $task->update(['status' => $status, 'completed_at' => $status === 'completed' ? now() : null]);
        if ($task->assigner) {
            $task->assigner->notify(new OfficerTaskUpdated($task, 'status'));
        }
        $incidentStatus = match ($status) {
            'handling' => 'on_process', 'completed' => 'extinguished', default => null
        };
        if ($incidentStatus && $task->incident->fire_status !== $incidentStatus) {
            $task->incident->update(['fire_status' => $incidentStatus]);
            IncidentStatusHistory::create(['incident_id' => $task->incident_id, 'updated_by' => $request->user()->id, 'status' => $incidentStatus, 'note' => $request->input('note')]);
        }
        $task->load(['incident.statusHistories', 'team.organization', 'acceptor']);

        return response()->json(['task' => $this->tasks->present($task)]);
    }
}
