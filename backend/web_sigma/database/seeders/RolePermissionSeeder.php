<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {

        $permissions = [

            // Dashboard
            'dashboard.view',

            // User
            'user.manage',

            // Karhutla
            'incident.create',
            'incident.view',
            'incident.update',
            'incident.delete',

            // GIS
            'map.view',
            'map.manage',

            // AI
            'ai.detect',
            'ai.manage',

            // Laporan
            'report.create',
            'report.verify',

            // Notifikasi
            'notification.send',

        ];

        foreach ($permissions as $permission) {

            Permission::create([
                'name' => $permission,
            ]);

        }

        // ROLE

        $superAdmin = Role::create([
            'name' => 'super_admin',
        ]);

        $government = Role::create([
            'name' => 'government',
        ]);

        $officer = Role::create([
            'name' => 'officer',
        ]);

        $citizen = Role::create([
            'name' => 'citizen',
        ]);

        // Permission role

        $superAdmin->givePermissionTo(
            Permission::all()
        );

        $government->givePermissionTo([
            'dashboard.view',
            'map.view',
            'map.manage',
            'incident.view',
            'report.verify',
            'notification.send',
        ]);

        $officer->givePermissionTo([
            'dashboard.view',
            'map.view',
            'incident.create',
            'incident.update',
            'report.create',
        ]);

        $citizen->givePermissionTo([
            'incident.create',
            'report.create',
            'map.view',
        ]);

    }
}
