<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiResponseRecommendation;
use App\Models\Incident;
use App\Models\User;

class AiResponseRecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $government = User::where(
            'email',
            'pemerintah@sigma.id'
        )->first();

        $incidents = Incident::orderBy('id')->get();

        foreach ($incidents as $incident) {

            AiResponseRecommendation::firstOrCreate(
                [
                    'incident_id' => $incident->id,
                    'recommendation_type' => 'deploy_team',
                ],
                [
                    'recommendation_text' =>
                        'Mengerahkan tim penanggulangan ke lokasi kejadian untuk melakukan verifikasi dan penanganan awal.',

                    'risk_summary' =>
                        'Rekomendasi berdasarkan tingkat keparahan incident yang terdeteksi.',

                    'confidence_score' => 85.00,

                    'model_version' => '1.0.0',

                    'decision_status' => 'pending',

                    'approved_by' => null,

                    'generated_at' => now(),
                ]
            );
        }
    }
}
