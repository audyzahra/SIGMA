<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncidentStatusHistory;
use App\Models\Incident;
use App\Models\User;

class IncidentStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        $incidents = Incident::orderBy('id')->get();

        $officer = User::where('email', 'petugas@sigma.id')->first();

        foreach ($incidents as $incident) {

            IncidentStatusHistory::firstOrCreate(
                [
                    'incident_id' => $incident->id,
                    'status' => $incident->fire_status,
                ],
                [
                    'updated_by' => $officer?->id,
                    'note' => 'Status incident tercatat oleh sistem SIGMA.',
                    'evidence_photo' => $incident->photo,
                ]
            );
        }
    }
}
