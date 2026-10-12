<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\ResponseAssignment;
use App\Models\FieldAssignment;
use App\Models\Incident;
use App\Models\FieldAssignmentStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResponseAssignmentController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
            'team_id' => ['required', 'exists:field_teams,id'],
            'priority_level' => [
                'required',
                'in:low,medium,high,critical',
            ],
            'incident_id' => [
                'nullable',
                'exists:incidents,id',
            ],
        ]);

        $incident = ! empty($validated['incident_id'])
            ? Incident::findOrFail($validated['incident_id'])
            : null;

        DB::transaction(function () use ($validated, $incident) {
            // Catat ResponseAssignment hanya jika ada insiden.
            if ($incident) {
                ResponseAssignment::create([
                    'incident_id' => $incident->id,
                    'team_id' => $validated['team_id'],
                    'assigned_by' => Auth::id(),
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);
            }


            $assignment = FieldAssignment::create([
                'incident_id' => $incident?->id,
                'region_id' => $validated['region_id'],
                'team_id' => $validated['team_id'],
                'assigned_by' => Auth::id(),
                'priority_level' => $validated['priority_level'],
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            FieldAssignmentStatusHistory::create([
                'field_assignment_id' => $assignment->id,
                'status' => 'assigned',
                'description' => 'Tim petugas ditugaskan untuk menangani wilayah.',
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('government.recommendation')
            ->with(
                'success',
                'Petugas berhasil dikirim dan wilayah masuk ke Riwayat Penanganan.'
            );
    }
}
