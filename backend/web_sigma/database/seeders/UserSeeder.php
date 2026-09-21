<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN
        |--------------------------------------------------------------------------
        */

        $superAdmin = User::firstOrCreate(
            [
                'email' => 'superadmin@sigma.id',
            ],
            [
                'name' => 'Super Admin SIGMA',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->syncRoles([
            'super_admin',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PEMERINTAH
        |--------------------------------------------------------------------------
        */

        $government = User::firstOrCreate(
            [
                'email' => 'pemerintah@sigma.id',
            ],
            [
                'name' => 'Pemerintah SIGMA',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $government->syncRoles([
            'government',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PETUGAS
        |--------------------------------------------------------------------------
        */

        $officer = User::firstOrCreate(
            [
                'email' => 'petugas@sigma.id',
            ],
            [
                'name' => 'Petugas SIGMA',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $officer->syncRoles([
            'officer',
        ]);


        /*
        |--------------------------------------------------------------------------
        | MASYARAKAT
        |--------------------------------------------------------------------------
        */

        $citizen = User::firstOrCreate(
            [
                'email' => 'masyarakat@sigma.id',
            ],
            [
                'name' => 'Masyarakat SIGMA',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $citizen->syncRoles([
            'citizen',
        ]);
    }
}
