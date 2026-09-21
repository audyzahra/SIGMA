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
        /*
        |--------------------------------------------------------------------------
        | PERMISSIONS
        |--------------------------------------------------------------------------
        */

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


<<<<<<< Updated upstream
=======
        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

>>>>>>> Stashed changes
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
        $government = Role::firstOrCreate([
            'name' => 'government',
            'guard_name' => 'web',
        ]);

<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
        $officer = Role::firstOrCreate([
            'name' => 'officer',
            'guard_name' => 'web',
        ]);

<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
        $citizen = Role::firstOrCreate([
            'name' => 'citizen',
            'guard_name' => 'web',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PERMISSION ROLE
        |--------------------------------------------------------------------------
        */

        $superAdmin->syncPermissions(
            Permission::all()
        );


        $government->syncPermissions([
            'dashboard.view',
            'map.view',
            'map.manage',
            'incident.view',
            'report.verify',
            'notification.send',
        ]);


        $officer->syncPermissions([
            'dashboard.view',
            'map.view',
            'incident.create',
            'incident.update',
            'report.create',
        ]);


        $citizen->syncPermissions([
            'incident.create',
            'report.create',
            'map.view',
        ]);
    }
}
