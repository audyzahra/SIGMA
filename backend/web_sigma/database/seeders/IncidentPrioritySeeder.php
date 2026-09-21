<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncidentPriority;
use App\Models\Incident;

class IncidentPrioritySeeder extends Seeder
{
    public function run(): void
    {
        $incidents = Incident::orderBy('id')->get();

        $ranking = 1;

        foreach ($incidents as $incident) {
            $riskScore = match ($incident->severity_level) {
                'low' => 25,
                'medium' => 50,
                'high' => 80,
            };

            $impactScore = match ($incident->severity_level) {
                'low' => 25,
                'medium' => 50,
                'high' => 75,
            };

            $priorityScore = (int) round(
                ($riskScore + $impactScore) / 2
            );

            $priorityLevel = match (true) {
                $priorityScore >= 75 => 'critical',
                $priorityScore >= 50 => 'high',
                $priorityScore >= 25 => 'medium',
                default => 'low',
            };

            IncidentPriority::firstOrCreate(
                [
                    'incident_id' => $incident->id,
                ],
                [
                    'risk_score' => $riskScore,
                    'impact_score' => $impactScore,
                    'priority_score' => $priorityScore,
                    'priority_level' => $priorityLevel,
                    'ranking_position' => $ranking,
                    'generated_by' => 'AI',
                ]
            );

            $ranking++;
        }
    }
}
