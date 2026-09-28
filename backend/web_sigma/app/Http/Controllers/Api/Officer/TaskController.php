<?php

namespace App\Http\Controllers\Api\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\UpdateTaskStatusRequest;
use App\Models\FieldAssignment;
use App\Models\IncidentStatusHistory;
use App\Notifications\OfficerTaskUpdated;
use App\Services\Officer\OfficerTaskService;
use App\Models\ReportStatusHistory;
use App\Models\CitizenReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function __construct(private readonly OfficerTaskService $tasks) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['tasks' => $this->tasks->forUser($request->user())->map(fn($task): array => $this->tasks->present($task))->values()]);
    }

    public function show(Request $request, FieldAssignment $task): JsonResponse
    {
        abort_unless($this->tasks->belongsToUser($request->user(), $task), 404);
        $task->load(['incident.statusHistories', 'team.organization', 'acceptor']);

        return response()->json(['task' => $this->tasks->present($task)]);
    }

    public function accept(Request $request, FieldAssignment $task): JsonResponse
    {
        Log::info('========== ACCEPT TASK START ==========', [
            'task_id' => $task->id,
            'incident_id' => $task->incident_id,
            'user_id' => $request->user()->id,
        ]);

        /*
    |--------------------------------------------------------------------------
    | Pastikan tugas milik petugas
    |--------------------------------------------------------------------------
    */

        abort_unless(
            $this->tasks->belongsToUser($request->user(), $task),
            404
        );

        /*
    |--------------------------------------------------------------------------
    | Terima tugas
    |--------------------------------------------------------------------------
    */

        if (!$task->accepted_at) {

            $task->update([
                'accepted_by' => $request->user()->id,
                'accepted_at' => now(),
            ]);

            Log::info('TASK ACCEPTED', [
                'task_id' => $task->id,
                'accepted_by' => $request->user()->id,
                'accepted_at' => $task->accepted_at,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Cari laporan masyarakat
    |--------------------------------------------------------------------------
    */

        $report = null;

        if ($task->incident_id) {

            $report = CitizenReport::where(
                'incident_id',
                $task->incident_id
            )->first();

            Log::info('CITIZEN REPORT FOUND', [
                'task_id' => $task->id,
                'incident_id' => $task->incident_id,
                'report_id' => $report?->id,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Buat history "Tugas Diterima Petugas"
    |--------------------------------------------------------------------------
    */

        if ($report) {

            $alreadyExists = ReportStatusHistory::where(
                'citizen_report_id',
                $report->id
            )
                ->where(
                    'description',
                    'Petugas telah menerima tugas penanganan.'
                )
                ->exists();

            if (!$alreadyExists) {

                $history = ReportStatusHistory::create([
                    'citizen_report_id' => $report->id,
                    'status' => 'process',
                    'description' => 'Petugas telah menerima tugas penanganan.',
                    'updated_by' => $request->user()->id,
                ]);

                Log::info('ACCEPT HISTORY CREATED', [
                    'history_id' => $history->id,
                    'report_id' => $report->id,
                    'description' => $history->description,
                ]);
            } else {

                Log::info('ACCEPT HISTORY ALREADY EXISTS', [
                    'report_id' => $report->id,
                ]);
            }
        } else {

            Log::error('CITIZEN REPORT NOT FOUND', [
                'task_id' => $task->id,
                'incident_id' => $task->incident_id,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Notifikasi ke Pemerintah
    |--------------------------------------------------------------------------
    */

        if ($task->assigner) {

            $task->assigner->notify(
                new OfficerTaskUpdated($task, 'accepted')
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Reload
    |--------------------------------------------------------------------------
    */

        $task->load([
            'incident.statusHistories',
            'team.organization',
            'acceptor',
        ]);

        Log::info('========== ACCEPT TASK END ==========');

        return response()->json([
            'task' => $this->tasks->present($task),
        ]);
    }

    public function updateStatus(
        UpdateTaskStatusRequest $request,
        FieldAssignment $task
    ): JsonResponse {
        abort_unless(
            $this->tasks->belongsToUser($request->user(), $task),
            404
        );

        abort_unless(
            $task->accepted_at,
            422,
            'Tugas harus diterima terlebih dahulu.'
        );

        $status = $request->string('status')->toString();

        /*
    |--------------------------------------------------------------------------
    | Update status tugas
    |--------------------------------------------------------------------------
    */

        $task->update([
            'status' => $status,
            'completed_at' => $status === 'completed'
                ? now()
                : null,
        ]);

        /*
    |--------------------------------------------------------------------------
    | Notifikasi ke Pemerintah
    |--------------------------------------------------------------------------
    */

        if ($task->assigner) {
            $task->assigner->notify(
                new OfficerTaskUpdated($task, 'status')
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Update status incident
    |--------------------------------------------------------------------------
    */

        $incidentStatus = match ($status) {

            'traveling',
            'arrived',
            'handling'
            => 'on_process',

            'completed'
            => 'extinguished',

            default
            => null
        };

        if (
            $incidentStatus &&
            $task->incident->fire_status !== $incidentStatus
        ) {
            $task->incident->update([
                'fire_status' => $incidentStatus,
            ]);

            IncidentStatusHistory::create([
                'incident_id' => $task->incident_id,
                'updated_by' => $request->user()->id,
                'status' => $incidentStatus,
                'note' => $request->input('note'),
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Update riwayat laporan masyarakat
    |--------------------------------------------------------------------------
    */

        if ($task->incident_id) {

            $report = CitizenReport::where(
                'incident_id',
                $task->incident_id
            )->first();

            if ($report) {

                $historyData = match ($status) {

                    'traveling' => [
                        'status' => 'process',
                        'description' =>
                        'Petugas sedang dalam perjalanan menuju lokasi kejadian.',
                    ],

                    'arrived' => [
                        'status' => 'process',
                        'description' =>
                        'Petugas telah tiba di lokasi kejadian.',
                    ],

                    'handling' => [
                        'status' => 'process',
                        'description' =>
                        'Petugas mulai melakukan penanganan di lokasi.',
                    ],

                    'completed' => [
                        'status' => 'completed',
                        'description' =>
                        'Petugas telah menyelesaikan penanganan kejadian.',
                    ],

                    default => null,
                };

                if ($historyData) {

                    /*
                |--------------------------------------------------------------------------
                | Hindari duplicate history
                |--------------------------------------------------------------------------
                */

                    $alreadyExists = ReportStatusHistory::where(
                        'citizen_report_id',
                        $report->id
                    )
                        ->where(
                            'description',
                            $historyData['description']
                        )
                        ->exists();

                    if (!$alreadyExists) {

                        ReportStatusHistory::create([
                            'citizen_report_id' => $report->id,
                            'status' => $historyData['status'],
                            'description' => $historyData['description'],
                            'updated_by' => $request->user()->id,
                        ]);
                    }
                }
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Reload task
    |--------------------------------------------------------------------------
    */

        $task->load([
            'incident.statusHistories',
            'team.organization',
            'acceptor',
        ]);

        return response()->json([
            'task' => $this->tasks->present($task),
        ]);
    }
}
