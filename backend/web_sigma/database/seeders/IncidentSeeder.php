<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Incident;

class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        $incidents = [
            [
                'source_type' => 'hotspot',
                'latitude' => -6.32745000,
                'longitude' => 108.32015000,
                'location_description' => 'Area lahan terbuka di Kabupaten Indramayu',
                'fire_status' => 'detected',
                'severity_level' => 'medium',
                'photo' => null,
                'detected_at' => now()->subHours(5),
            ],
            [
                'source_type' => 'hotspot',
                'latitude' => -6.36521000,
                'longitude' => 108.37542000,
                'location_description' => 'Area perkebunan di Kabupaten Indramayu',
                'fire_status' => 'on_process',
                'severity_level' => 'high',
                'photo' => null,
                'detected_at' => now()->subHours(4),
            ],
            [
                'source_type' => 'report',
                'latitude' => -6.41235000,
                'longitude' => 108.29587000,
                'location_description' => 'Area pertanian yang dilaporkan masyarakat',
                'fire_status' => 'detected',
                'severity_level' => 'low',
                'photo' => null,
                'detected_at' => now()->subHours(8),
            ],
            [
                'source_type' => 'manual',
                'latitude' => -6.28975000,
                'longitude' => 108.41023000,
                'location_description' => 'Lokasi kejadian yang dicatat secara manual oleh petugas',
                'fire_status' => 'extinguished',
                'severity_level' => 'medium',
                'photo' => null,
                'detected_at' => now()->subDay(),
            ],
        ];

        foreach ($incidents as $incident) {
            Incident::create($incident);
        }
    }
}
