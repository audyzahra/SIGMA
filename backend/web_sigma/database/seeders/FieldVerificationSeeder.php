<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FieldVerification;
use App\Models\Incident;
use App\Models\FieldTeam;

class FieldVerificationSeeder extends Seeder
{
    public function run(): void
    {
        $incidents = Incident::orderBy('id')->get();
        $teams = FieldTeam::orderBy('id')->get();

        if ($teams->isEmpty()) {
            return;
        }

        foreach ($incidents as $index => $incident) {
            $team = $teams[$index % $teams->count()];

            FieldVerification::firstOrCreate(
                [
                    'incident_id' => $incident->id,
                    'team_id' => $team->id,
                ],
                [
                    'photo' => $incident->photo,
                    'video' => null,
                    'description' => 'Verifikasi lapangan dilakukan oleh tim SIGMA.',
                    'verified_at' => now(),
                ]
            );
        }
    }
}
