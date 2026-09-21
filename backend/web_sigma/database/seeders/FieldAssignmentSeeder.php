<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FieldAssignment;
use App\Models\Incident;
use App\Models\FieldTeam;
use App\Models\User;

class FieldAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $government = User::where(
            'email',
            'pemerintah@sigma.id'
        )->first();

        if (!$government) {
            return;
        }

        $incidents = Incident::orderBy('id')->get();

        $teams = FieldTeam::orderBy('id')->get();

        if ($teams->isEmpty()) {
            return;
        }

        foreach ($incidents as $index => $incident) {
            $team = $teams[$index % $teams->count()];

            $priorityLevel = match ($incident->severity_level) {
                'low' => 'low',
                'medium' => 'medium',
                'high' => 'high',
            };

            $status = match ($incident->fire_status) {
                'detected' => 'assigned',
                'on_process' => 'handling',
                'extinguished' => 'completed',
            };

            FieldAssignment::firstOrCreate(
                [
                    'incident_id' => $incident->id,
                    'team_id' => $team->id,
                ],
                [
                    'assigned_by' => $government->id,
                    'priority_level' => $priorityLevel,
                    'status' => $status,
                    'assigned_at' => now()->subHours($index + 1),
                    'completed_at' => $status === 'completed'
                        ? now()->subHours($index)
                        : null,
                ]
            );
        }
    }
}
