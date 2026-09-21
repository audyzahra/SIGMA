<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemConfiguration;

class SystemConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configurations = [
            [
                'key' => 'system_name',
                'value' => 'SIGMA',
                'type' => 'string',
                'description' => 'Nama sistem aplikasi SIGMA.',
            ],
            [
                'key' => 'system_tagline',
                'value' => 'Sistem Intelijen Geospasial untuk Mitigasi Karhutla',
                'type' => 'string',
                'description' => 'Tagline sistem SIGMA.',
            ],
            [
                'key' => 'hotspot_detection_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Mengaktifkan fitur deteksi hotspot pada sistem.',
            ],
            [
                'key' => 'ai_detection_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Mengaktifkan fitur deteksi berbasis AI.',
            ],
            [
                'key' => 'notification_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Mengaktifkan sistem notifikasi SIGMA.',
            ],
            [
                'key' => 'hotspot_sync_interval',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Interval sinkronisasi data hotspot dalam menit.',
            ],
        ];

        foreach ($configurations as $configuration) {
            SystemConfiguration::firstOrCreate(
                [
                    'key' => $configuration['key'],
                ],
                [
                    'value' => $configuration['value'],
                    'type' => $configuration['type'],
                    'description' => $configuration['description'],
                ]
            );
        }
    }
}
