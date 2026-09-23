<?php

namespace App\Services\Officer;

use App\Models\FieldAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class OfficerTaskService
{
    /** @return Collection<int, FieldAssignment> */
    public function forUser(User $user): Collection
    {
        return FieldAssignment::query()
            ->whereIn('team_id', $user->fieldTeams()->select('field_teams.id'))
            ->with(['incident.statusHistories', 'team.organization', 'acceptor'])
            ->latest('assigned_at')
            ->get();
    }

    public function belongsToUser(User $user, FieldAssignment $assignment): bool
    {
        return $user->fieldTeams()->whereKey($assignment->team_id)->exists();
    }

    /** @return array<string, mixed> */
    public function present(FieldAssignment $assignment): array
    {
        $incident = $assignment->incident;

        return [
            'id' => $assignment->id,
            'status' => $assignment->status,
            'priority_level' => $assignment->priority_level,
            'assigned_at' => $assignment->assigned_at?->toIso8601String(),
            'accepted_at' => $assignment->accepted_at?->toIso8601String(),
            'is_accepted' => $assignment->accepted_at !== null,
            'team' => [
                'id' => $assignment->team->id,
                'name' => $assignment->team->team_name,
                'organization' => $assignment->team->organization?->name,
            ],
            'incident' => [
                'id' => $incident->id,
                'source_type' => $incident->source_type,
                'location_description' => $incident->location_description,
                'latitude' => (float) $incident->latitude,
                'longitude' => (float) $incident->longitude,
                'fire_status' => $incident->fire_status,
                'severity_level' => $incident->severity_level,
                'detected_at' => $incident->detected_at?->toIso8601String(),
                'history' => $incident->statusHistories->sortBy('created_at')->values()->map(fn ($history): array => [
                    'status' => $history->status,
                    'note' => $history->note,
                    'created_at' => $history->created_at?->toIso8601String(),
                ]),
            ],
        ];
    }
}
