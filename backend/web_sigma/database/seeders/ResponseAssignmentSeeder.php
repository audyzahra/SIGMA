<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ResponseAssignment;
use App\Models\Incident;
use App\Models\FieldTeam;
use App\Models\User;

class ResponseAssignmentSeeder extends Seeder
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

            ResponseAssignment::firstOrCreate(
                [
                    'incident_id' => $incident->id,
                    'team_id' => $team->id,
                ],
                [
                    'assigned_by' => $government->id,
                    'status' => match ($incident->fire_status) {
                        'detected' => 'assigned',
                        'on_process' => 'progress',
                        'extinguished' => 'completed',
                    },
                    'assigned_at' => now()->subHours($index + 1),
                ]
            );
        }
    }
}
