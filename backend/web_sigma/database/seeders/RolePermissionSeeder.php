<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {

        app(PermissionRegistrar::class)->forgetCachedPermissions();

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

            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);

        }

        // ROLE

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $government = Role::firstOrCreate([
            'name' => 'government',
            'guard_name' => 'web',
        ]);

        $officer = Role::firstOrCreate([
            'name' => 'officer',
            'guard_name' => 'web',
        ]);

        $citizen = Role::firstOrCreate([
            'name' => 'citizen',
            'guard_name' => 'web',
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