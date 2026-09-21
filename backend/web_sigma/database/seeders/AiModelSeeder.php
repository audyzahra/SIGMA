<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiModel;
use App\Models\User;

class AiModelSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where(
            'email',
            'superadmin@sigma.id'
        )->first();

        AiModel::firstOrCreate(
            [
                'code' => 'PADI-DISEASE-V1',
            ],
            [
                'name' => 'Padi Disease Detection Model',
                'version' => '1.0.0',
                'description' => 'Model AI untuk mendeteksi penyakit tanaman padi berdasarkan citra.',
                'type' => 'detection',
                'input_type' => 'image',
                'output_type' => 'disease classification',
                'framework' => 'YOLO',
                'algorithm' => 'YOLOv11',
                'model_file' => 'padi.pt',
                'model_size' => null,
                'accuracy' => null,
                'precision_score' => null,
                'recall_score' => null,
                'f1_score' => null,
                'training_dataset' => null,
                'trained_at' => null,
                'deployed_at' => null,
                'endpoint_url' => null,
                'status' => 'testing',
                'created_by' => $superAdmin?->id,
            ]
        );

        AiModel::firstOrCreate(
            [
                'code' => 'FIRE-RISK-PREDICTION-V1',
            ],
            [
                'name' => 'Fire Risk Prediction Model',
                'version' => '1.0.0',
                'description' => 'Model AI untuk membantu prediksi tingkat risiko kebakaran hutan dan lahan.',
                'type' => 'prediction',
                'input_type' => 'geo',
                'output_type' => 'risk level',
                'framework' => null,
                'algorithm' => null,
                'model_file' => null,
                'model_size' => null,
                'accuracy' => null,
                'precision_score' => null,
                'recall_score' => null,
                'f1_score' => null,
                'training_dataset' => null,
                'trained_at' => null,
                'deployed_at' => null,
                'endpoint_url' => null,
                'status' => 'testing',
                'created_by' => $superAdmin?->id,
            ]
        );

        AiModel::firstOrCreate(
            [
                'code' => 'FIRE-RECOMMENDATION-V1',
            ],
            [
                'name' => 'Fire Response Recommendation Model',
                'version' => '1.0.0',
                'description' => 'Model AI untuk memberikan rekomendasi respons terhadap insiden kebakaran.',
                'type' => 'recommendation',
                'input_type' => 'geo',
                'output_type' => 'response recommendation',
                'framework' => null,
                'algorithm' => null,
                'model_file' => null,
                'model_size' => null,
                'accuracy' => null,
                'precision_score' => null,
                'recall_score' => null,
                'f1_score' => null,
                'training_dataset' => null,
                'trained_at' => null,
                'deployed_at' => null,
                'endpoint_url' => null,
                'status' => 'testing',
                'created_by' => $superAdmin?->id,
            ]
        );
    }
}
