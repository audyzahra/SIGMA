<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiReportVerification;
use App\Models\CitizenReport;
use App\Models\AiModel;

class AiReportVerificationSeeder extends Seeder
{
    public function run(): void
    {
        $reports = CitizenReport::orderBy('id')->get();
        $models = AiModel::orderBy('id')->get();

        if ($reports->isEmpty() || $models->isEmpty()) {
            return;
        }

        foreach ($reports as $index => $report) {
            $model = $models[$index % $models->count()];

            AiReportVerification::firstOrCreate(
                [
                    'citizen_report_id' => $report->id,
                ],
                [
                    'model_id' => $model->id,
                    'prediction_result' => 'fire',
                    'confidence_score' => 92.50,
                    'detected_objects' => [
                        'fire' => 0.925,
                        'smoke' => 0.150,
                    ],
                    'verification_status' => 'reviewed',
                    'verified_at' => now(),
                ]
            );
        }
    }
}
