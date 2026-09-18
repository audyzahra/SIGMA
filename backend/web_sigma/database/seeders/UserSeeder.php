<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        // SUPER ADMIN

        $superAdmin = User::create([
            'name' => 'Super Admin SIGMA',
            'email' => 'superadmin@sigma.id',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $superAdmin->assignRole('super_admin');



        // PEMERINTAH

        $government = User::create([
            'name' => 'Pemerintah SIGMA',
            'email' => 'pemerintah@sigma.id',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $government->assignRole('government');



        // PETUGAS DEFAULT

        $officer = User::create([
            'name' => 'Petugas SIGMA',
            'email' => 'petugas@sigma.id',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $officer->assignRole('officer');



        // MASYARAKAT DEFAULT

        $citizen = User::create([
            'name' => 'Masyarakat SIGMA',
            'email' => 'masyarakat@sigma.id',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $citizen->assignRole('citizen');

    }
}
