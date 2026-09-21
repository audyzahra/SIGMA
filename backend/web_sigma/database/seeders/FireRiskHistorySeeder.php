<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FireRiskHistory;
use App\Models\Region;

class FireRiskHistorySeeder extends Seeder
{
    public function run(): void
    {
        $regions = Region::where('level', 'district')
            ->get();

        foreach ($regions as $region) {
            $history = [
                [
                    'region_id' => $region->id,
                    'risk_score' => 30,
                    'risk_level' => 'low',
                    'calculated_at' => now()->subDays(2),
                ],
                [
                    'region_id' => $region->id,
                    'risk_score' => 35,
                    'risk_level' => 'low',
                    'calculated_at' => now()->subDay(),
                ],
                [
                    'region_id' => $region->id,
                    'risk_score' => 40,
                    'risk_level' => 'medium',
                    'calculated_at' => now(),
                ],
            ];

            foreach ($history as $data) {
                FireRiskHistory::firstOrCreate(
                    [
                        'region_id' => $data['region_id'],
                        'calculated_at' => $data['calculated_at'],
                    ],
                    [
                        'risk_score' => $data['risk_score'],
                        'risk_level' => $data['risk_level'],
                    ]
                );
            }
        }
    }
}
