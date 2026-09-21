<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FireStatistic;

class FireStatisticSeeder extends Seeder
{
    public function run(): void
    {
        $statistics = [
            [
                'statistic_date' => now()->subDays(2)->toDateString(),
                'total_hotspots' => 12,
                'active_incidents' => 3,
                'resolved_incident' => 5,
                'affected_area' => 25,
                'affected_region' => 2,
            ],
            [
                'statistic_date' => now()->subDay()->toDateString(),
                'total_hotspots' => 15,
                'active_incidents' => 4,
                'resolved_incident' => 6,
                'affected_area' => 30,
                'affected_region' => 3,
            ],
            [
                'statistic_date' => now()->toDateString(),
                'total_hotspots' => 18,
                'active_incidents' => 5,
                'resolved_incident' => 7,
                'affected_area' => 35,
                'affected_region' => 3,
            ],
        ];

        foreach ($statistics as $statistic) {
            FireStatistic::firstOrCreate(
                [
                    'statistic_date' => $statistic['statistic_date'],
                ],
                [
                    'total_hotspots' => $statistic['total_hotspots'],
                    'active_incidents' => $statistic['active_incidents'],
                    'resolved_incident' => $statistic['resolved_incident'],
                    'affected_area' => $statistic['affected_area'],
                    'affected_region' => $statistic['affected_region'],
                ]
            );
        }
    }
}
