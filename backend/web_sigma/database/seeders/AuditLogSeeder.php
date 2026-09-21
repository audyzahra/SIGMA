<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'superadmin@sigma.id')->first();

        if (!$admin) {
            return;
        }

        $logs = [
            [
                'user_id' => $admin->id,
                'action' => 'create',
                'module' => 'users',
                'description' => 'Super Admin membuat data pengguna baru.',
                'old_values' => null,
                'new_values' => [
                    'role' => 'citizen',
                    'status' => 'active',
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'SIGMA Seeder',
            ],
            [
                'user_id' => $admin->id,
                'action' => 'update',
                'module' => 'incidents',
                'description' => 'Status incident diperbarui oleh Super Admin.',
                'old_values' => [
                    'fire_status' => 'detected',
                ],
                'new_values' => [
                    'fire_status' => 'on_process',
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'SIGMA Seeder',
            ],
            [
                'user_id' => $admin->id,
                'action' => 'create',
                'module' => 'ai_models',
                'description' => 'Model AI ditambahkan ke sistem SIGMA.',
                'old_values' => null,
                'new_values' => [
                    'status' => 'testing',
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'SIGMA Seeder',
            ],
        ];

        foreach ($logs as $log) {
            AuditLog::create($log);
        }
    }
}
