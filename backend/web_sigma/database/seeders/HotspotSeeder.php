<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotspot;
use App\Models\DataSource;

class HotspotSeeder extends Seeder
{
    public function run(): void
    {
        $satelliteSource = DataSource::where(
            'type',
            'satellite'
        )->first();

        if (!$satelliteSource) {
            return;
        }

        $hotspots = [
            [
                'latitude' => -6.32745000,
                'longitude' => 108.32015000,
                'satellite_name' => 'MODIS',
                'brightness_temperature' => 328.50,
                'confidence_level' => 85,
                'detected_at' => now()->subHours(6),
                'status' => 'active',
            ],
            [
                'latitude' => -6.36521000,
                'longitude' => 108.37542000,
                'satellite_name' => 'VIIRS',
                'brightness_temperature' => 335.20,
                'confidence_level' => 91,
                'detected_at' => now()->subHours(4),
                'status' => 'active',
            ],
            [
                'latitude' => -6.41235000,
                'longitude' => 108.29587000,
                'satellite_name' => 'MODIS',
                'brightness_temperature' => 322.80,
                'confidence_level' => 76,
                'detected_at' => now()->subHours(10),
                'status' => 'resolved',
            ],
            [
                'latitude' => -6.28975000,
                'longitude' => 108.41023000,
                'satellite_name' => 'VIIRS',
                'brightness_temperature' => 331.40,
                'confidence_level' => 88,
                'detected_at' => now()->subHours(2),
                'status' => 'active',
            ],
            [
                'latitude' => -6.44812000,
                'longitude' => 108.35264000,
                'satellite_name' => 'MODIS',
                'brightness_temperature' => 326.70,
                'confidence_level' => 82,
                'detected_at' => now()->subDay(),
                'status' => 'resolved',
            ],
        ];

        foreach ($hotspots as $hotspot) {
            Hotspot::firstOrCreate(
                [
                    'source_id' => $satelliteSource->id,
                    'latitude' => $hotspot['latitude'],
                    'longitude' => $hotspot['longitude'],
                    'detected_at' => $hotspot['detected_at'],
                ],
                [
                    'satellite_name' => $hotspot['satellite_name'],
                    'brightness_temperature' => $hotspot['brightness_temperature'],
                    'confidence_level' => $hotspot['confidence_level'],
                    'status' => $hotspot['status'],
                ]
            );
        }
    }
}
