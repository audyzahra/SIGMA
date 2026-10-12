<?php

namespace App\Services\Officer;

use App\Models\FieldAssignment;
use App\Models\User;
use App\Models\PriorityResult;
use App\Models\FieldAssignmentStatusHistory;
use Illuminate\Database\Eloquent\Collection;

class OfficerTaskService
{
    /** @return Collection<int, FieldAssignment> */
    public function forUser(User $user): Collection
    {
        return FieldAssignment::query()
            ->whereIn(
                'team_id',
                $user->fieldTeams()->select('field_teams.id')
            )

            ->with([
                'incident.statusHistories',
                'statusHistories.updater',
                'region',
                'team.organization',
                'acceptor',
            ])

            ->latest('assigned_at')
            ->get();
    }

    public function belongsToUser(
        User $user,
        FieldAssignment $assignment
    ): bool {
        return $user->fieldTeams()
            ->whereKey($assignment->team_id)
            ->exists();
    }

    /** @return array<string, mixed> */
    public function present(FieldAssignment $assignment): array
    {
        $incident = $assignment->incident;
        $team = $assignment->team;
        $region = $assignment->region;

        $priorityResult = $assignment->region_id
            ? PriorityResult::query()
            ->where('region_id', $assignment->region_id)
            ->orderByDesc('calculated_at')
            ->first()
            : null;

        $location = $incident?->location_description
            ?? $region?->name
            ?? $region?->region_name
            ?? 'Lokasi wilayah belum ditentukan';

        $status = $assignment->accepted_at !== null
            && $assignment->status === 'assigned'
            ? 'accepted'
            : $assignment->status;

        return [
            'id' => $assignment->id,
            'status' => $status,
            'priority_level' => $assignment->priority_level,
            'assigned_at' => $assignment->assigned_at?->toIso8601String(),
            'accepted_at' => $assignment->accepted_at?->toIso8601String(),
            'is_accepted' => $assignment->accepted_at !== null,

            'team' => [
                'id' => $team?->id,
                'name' => $team?->team_name,
                'organization' => $team?->organization?->name,
            ],

            'region' => $region ? [
                'id' => $region->id,
                'name' => $region->name
                    ?? $region->region_name
                    ?? 'Wilayah',
            ] : null,

            'incident' => $incident ? [
                'id' => $incident->id,
                'source_type' => $incident->source_type,
                'location_description' => $incident->location_description,
                'latitude' => $incident->latitude !== null
                    ? (float) $incident->latitude
                    : null,
                'longitude' => $incident->longitude !== null
                    ? (float) $incident->longitude
                    : null,
                'fire_status' => $incident->fire_status,
                'severity_level' => $incident->severity_level,
                'detected_at' => $incident->detected_at?->toIso8601String(),

                'history' => $incident->statusHistories
                    ->sortBy('created_at')
                    ->values()
                    ->map(fn($history): array => [
                        'status' => $history->status,
                        'note' => $history->note,
                        'created_at' => $history->created_at
                            ?->toIso8601String(),
                    ])
                    ->all(),
            ] : null,

            'location_description' => $location,

            'scores' => [
                'risk_score' => $priorityResult?->risk_score,
                'impact_score' => $priorityResult?->impact_score,
                'priority_score' => $priorityResult?->priority_score,
                'ranking_position' => $priorityResult?->ranking_position,
            ],

            'latitude' => $incident?->latitude !== null
                ? (float) $incident->latitude
                : null,
            'longitude' => $incident?->longitude !== null
                ? (float) $incident->longitude
                : null,



            'assignment_history' => $assignment->statusHistories
                ->sortBy('created_at')
                ->values()
                ->map(fn($history): array => [
                    'status' => $history->status,
                    'description' => $history->description,
                    'updated_by' => $history->updater?->name,
                    'created_at' => $history->created_at?->toIso8601String(),
                ])
                ->all(),

        ];
    }
}
