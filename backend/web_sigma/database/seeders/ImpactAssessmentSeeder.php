<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImpactAssessment;
use App\Models\Incident;

class ImpactAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $incidents = Incident::all();

        foreach ($incidents as $incident) {
            ImpactAssessment::firstOrCreate(
                [
                    'incident_id' => $incident->id,
                ],
                [
                    'affected_population' => 150,
                    'affected_households' => 45,
                    'affected_area' => 12.50,
                    'forest_area' => 5.00,
                    'peatland_area' => 2.50,
                    'school_count' => 1,
                    'hospital_count' => 0,
                    'road_distance' => 2.50,
                    'impact_score' => 40,
                    'calculated_at' => now(),
                ]
            );
        }
    }
}
