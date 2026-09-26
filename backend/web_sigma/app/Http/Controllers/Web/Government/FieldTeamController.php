<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\FieldTeam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FieldTeamController extends Controller
{

    public function index()
    {
        $teams = FieldTeam::with('members')
            ->latest()
            ->get();

        return view(
            'government.field-teams.index',
            compact('teams')
        );
    }



    public function create()
    {

        $officers = User::role('officer')->get();

        return view(
            'government.field-teams.create',
            compact('officers')
        );
    }



    public function store(Request $request)
    {

        $request->validate([

            'team_name' => 'required',
            'members' => 'required|array'

        ]);


        $team = FieldTeam::create([

            'organization_id' => Auth::user()->organization_id,

            'team_name' => $request->team_name,

            'leader_name' => $request->leader_name,

            'phone' => $request->phone,

            'status' => 'active'

        ]);


        $team->members()->attach(
            $request->members,
            [
                'online_status' => 'offline'
            ]
        );


        return redirect()
            ->route('government.field-teams.index')
            ->with(
                'success',
                'Tim berhasil dibuat'
            );
    }


    public function edit(FieldTeam $team)
    {
        $officers = User::role('officer')->get();

        $team->load('members');

        return view(
            'government.field-teams.edit',
            compact(
                'team',
                'officers'
            )
        );
    }


    public function update(Request $request, FieldTeam $team)
    {

        $request->validate([

            'team_name' => 'required',
            'members' => 'required|array'

        ]);


        $team->update([

            'team_name' => $request->team_name,

            'leader_name' => $request->leader_name,

            'phone' => $request->phone,

        ]);


        $team->members()->sync(
            $request->members ?? []
        );


        return redirect()
            ->route('government.field-teams.index')
            ->with(
                'success',
                'Tim berhasil diperbarui'
            );
    }


    public function destroy(FieldTeam $team)
    {

        $team->members()->detach();

        $team->delete();


        return redirect()
            ->route('government.field-teams.index')
            ->with(
                'success',
                'Tim berhasil dihapus'
            );
    }
}
