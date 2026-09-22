<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FireRisk;
use App\Models\Region;

class FireRiskSeeder extends Seeder
{
    public function run(): void
    {
        $regions = Region::where('level', 'district')
            ->orderBy('id')
            ->get();

        $riskData = [
            [
                'risk_score' => 35,
                'risk_level' => 'low',
                'temperature' => 30.50,
                'humidity' => 70.00,
                'rainfall' => 5.00,
                'wind_speed' => 10.00,
                'vegetation_index' => 0.6500,
            ],
            [
                'risk_score' => 55,
                'risk_level' => 'medium',
                'temperature' => 32.00,
                'humidity' => 60.00,
                'rainfall' => 3.00,
                'wind_speed' => 12.00,
                'vegetation_index' => 0.5800,
            ],
            [
                'risk_score' => 72,
                'risk_level' => 'high',
                'temperature' => 33.50,
                'humidity' => 50.00,
                'rainfall' => 2.00,
                'wind_speed' => 15.00,
                'vegetation_index' => 0.4800,
            ],
            [
                'risk_score' => 88,
                'risk_level' => 'extreme',
                'temperature' => 35.00,
                'humidity' => 42.00,
                'rainfall' => 1.00,
                'wind_speed' => 20.00,
                'vegetation_index' => 0.3500,
            ],
            [
                'risk_score' => 64,
                'risk_level' => 'high',
                'temperature' => 34.00,
                'humidity' => 55.00,
                'rainfall' => 4.00,
                'wind_speed' => 17.00,
                'vegetation_index' => 0.4200,
            ],
        ];

        foreach ($regions as $index => $region) {
            $data = $riskData[$index] ?? $riskData[0];

            FireRisk::updateOrCreate(
                [
                    'region_id' => $region->id,
                    'calculated_at' => now()->startOfDay(),
                ],
                $data
            );
        }
    }
}