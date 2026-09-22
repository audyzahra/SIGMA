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

        $recommendations = [
            [
                'type' => 'deploy_team',
                'text' => 'Mengerahkan tim penanggulangan ke lokasi kejadian untuk melakukan verifikasi dan penanganan awal.',
                'summary' => 'Tim pemadam perlu dikerahkan untuk melakukan verifikasi dan penanganan awal di lokasi kejadian.',
            ],

            [
                'type' => 'aerial_patrol',
                'text' => 'Melakukan patroli udara menggunakan drone atau helikopter untuk memantau perkembangan kejadian dan area sekitar.',
                'summary' => 'Patroli udara digunakan untuk memperoleh pemantauan kondisi lokasi dan area terdampak dari udara.',
            ],

            [
                'type' => 'community_alert',
                'text' => 'Mendistribusikan peringatan kepada masyarakat di wilayah sekitar lokasi kejadian agar meningkatkan kewaspadaan.',
                'summary' => 'Peringatan masyarakat diperlukan untuk meningkatkan kewaspadaan di sekitar lokasi kejadian.',
            ],

            [
                'type' => 'water_source_check',
                'text' => 'Melakukan pengecekan sumber air terdekat untuk memastikan ketersediaan air bagi proses penanganan kebakaran.',
                'summary' => 'Ketersediaan sumber air perlu diperiksa untuk mendukung proses pemadaman.',
            ],
        ];

        foreach ($incidents as $incident) {

            foreach ($recommendations as $recommendation) {

                AiResponseRecommendation::updateOrCreate(
                    [
                        'incident_id' => $incident->id,
                        'recommendation_type' => $recommendation['type'],
                    ],
                    [
                        'recommendation_text' => $recommendation['text'],

                        'risk_summary' => $recommendation['summary'],

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
}