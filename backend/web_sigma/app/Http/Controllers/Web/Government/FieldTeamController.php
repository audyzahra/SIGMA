<?php

namespace App\Http\Controllers\Web\Government;

use App\Helpers\EncryptHelper;
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
            ->paginate(10);

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

            'team_name' => 'required|string|max:255',
            'leader_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'members' => 'required|array',
            'members.*' => 'exists:users,id',

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


    public function edit(string $team)
    {
        $id = EncryptHelper::decrypt($team);

        $team = FieldTeam::with('members')
            ->findOrFail($id);


        $officers = User::role('officer')->get();


        return view(
            'government.field-teams.edit',
            compact(
                'team',
                'officers'
            )
        );
    }


    public function update(Request $request, string $team)
    {

        $id = EncryptHelper::decrypt($team);

        $team = FieldTeam::findOrFail($id);

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

    public function show(string $team)
    {

        $id = EncryptHelper::decrypt($team);


        $team = FieldTeam::with('members')
            ->findOrFail($id);



        return view(
            'government.field-teams.show',
            compact('team')
        );
    }


    public function destroy(string $team)
    {

        $id = EncryptHelper::decrypt($team);

        $team = FieldTeam::findOrFail($id);


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
