<?php

namespace App\Http\Controllers\Api\Officer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['profile' => ['name' => $user->name, 'email' => $user->email, 'roles' => $user->getRoleNames()->values(), 'teams' => $user->fieldTeams()->with('organization.region')->get()->map(fn ($team): array => ['name' => $team->team_name, 'online_status' => $team->pivot->online_status, 'last_seen_at' => $team->pivot->last_seen_at, 'organization' => $team->organization?->name, 'region' => $team->organization?->region?->name])->values()]]);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->fieldTeams()->updateExistingPivot($user->fieldTeams()->pluck('field_teams.id'), ['online_status' => 'online', 'last_seen_at' => now()]);

        return response()->json(['status' => 'online']);
    }
}
