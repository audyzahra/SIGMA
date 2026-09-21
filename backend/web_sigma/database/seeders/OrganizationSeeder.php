<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Region;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil region Kabupaten Indramayu
        $indramayu = Region::where('code', '3212')->first();

        // PEMERINTAH
        Organization::firstOrCreate(
            [
                'name' => 'Pemerintah Kabupaten Indramayu',
            ],
            [
                'type' => 'government',
                'address' => 'Kabupaten Indramayu, Jawa Barat',
                'phone' => null,
                'email' => 'pemerintah@indramayukab.go.id',
                'region_id' => $indramayu?->id,
                'status' => 'active',
            ]
        );

        // TIM PENANGGULANGAN KARHUTLA
        Organization::firstOrCreate(
            [
                'name' => 'Tim Penanggulangan Karhutla SIGMA',
            ],
            [
                'type' => 'team',
                'address' => 'Kabupaten Indramayu, Jawa Barat',
                'phone' => null,
                'email' => 'tim@sigmadata.id',
                'region_id' => $indramayu?->id,
                'status' => 'active',
            ]
        );

        // ORGANISASI / PERUSAHAAN
        Organization::firstOrCreate(
            [
                'name' => 'Mitra SIGMA',
            ],
            [
                'type' => 'company',
                'address' => 'Kabupaten Indramayu, Jawa Barat',
                'phone' => null,
                'email' => 'mitra@sigmadata.id',
                'region_id' => $indramayu?->id,
                'status' => 'active',
            ]
        );
    }
}
