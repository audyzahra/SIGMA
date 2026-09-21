<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeamNavigationLog;
use App\Models\FieldAssignment;

class TeamNavigationLogSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = FieldAssignment::orderBy('id')->get();

        foreach ($assignments as $assignment) {
            $incident = $assignment->incident;

            if (!$incident) {
                continue;
            }

            TeamNavigationLog::firstOrCreate(
                [
                    'assignment_id' => $assignment->id,
                ],
                [
                    'current_latitude' => $incident->latitude - 0.01000000,
                    'current_longitude' => $incident->longitude - 0.01000000,

                    'destination_latitude' => $incident->latitude,
                    'destination_longitude' => $incident->longitude,

                    'distance' => 1.50,
                    'estimated_time' => 15,
                ]
            );
        }
    }
}
