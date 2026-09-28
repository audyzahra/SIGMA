<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ResponseAssignment;
use App\Models\FieldAssignment;
use Illuminate\Support\Facades\Auth;


class ResponseAssignmentController extends Controller
{

    public function store(Request $request)
    {

        $request->validate([

            'incident_id' => 'required',
            'team_id' => 'required',

        ]);


        ResponseAssignment::create([
            'incident_id' => $request->incident_id,
            'team_id' => $request->team_id,
            'assigned_by' => Auth::id(),
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);


        FieldAssignment::create([
            'incident_id' => $request->incident_id,
            'team_id' => $request->team_id,
            'assigned_by' => Auth::id(),
            'priority_level' => 'high',
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);


        return back()->with(
            'success',
            'Petugas berhasil dikirim'
        );
    }
}
