<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OfflineSyncQueue;

class OfflineSyncQueueSeeder extends Seeder
{
    public function run(): void
    {
        $queues = [
            [
                'data_type' => 'incident_update',
                'payload' => [
                    'incident_id' => 1,
                    'fire_status' => 'on_process',
                    'description' => 'Update status incident dari perangkat petugas.',
                ],
                'sync_status' => 'synced',
                'device_id' => 'DEVICE-SIGMA-001',
            ],
            [
                'data_type' => 'field_verification',
                'payload' => [
                    'incident_id' => 2,
                    'team_id' => 1,
                    'description' => 'Data verifikasi lapangan menunggu sinkronisasi.',
                ],
                'sync_status' => 'pending',
                'device_id' => 'DEVICE-SIGMA-002',
            ],
            [
                'data_type' => 'navigation_log',
                'payload' => [
                    'assignment_id' => 1,
                    'current_latitude' => -6.33745,
                    'current_longitude' => 108.31015,
                ],
                'sync_status' => 'pending',
                'device_id' => 'DEVICE-SIGMA-001',
            ],
        ];

        foreach ($queues as $queue) {
            OfflineSyncQueue::firstOrCreate(
                [
                    'data_type' => $queue['data_type'],
                    'device_id' => $queue['device_id'],
                ],
                [
                    'payload' => $queue['payload'],
                    'sync_status' => $queue['sync_status'],
                ]
            );
        }
    }
}
