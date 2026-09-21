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
            ->get();

        foreach ($regions as $region) {
            FireRisk::firstOrCreate(
                [
                    'region_id' => $region->id,
                    'calculated_at' => now()->startOfDay(),
                ],
                [
                    'risk_score' => 35,
                    'risk_level' => 'low',
                    'temperature' => 30.50,
                    'humidity' => 70.00,
                    'rainfall' => 5.00,
                    'wind_speed' => 10.00,
                    'vegetation_index' => 0.6500,
                ]
            );
        }
    }
}
