<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemProfile;

class SystemProfileSeeder extends Seeder
{
    public function run(): void
    {
        SystemProfile::firstOrCreate(
            [
                'title' => 'SIGMA',
            ],
            [
                'tagline' => 'Sistem Intelijen Geospasial untuk Mitigasi Karhutla',
                'hero_image' => null,
                'total_hotspot_label' => 'Total Hotspot',
                'total_incident_label' => 'Total Insiden',
                'contact_email' => 'info@sigma.id',
                'contact_phone' => null,
            ]
        );
    }
}
