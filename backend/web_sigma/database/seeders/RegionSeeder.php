<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PROVINSI
        |--------------------------------------------------------------------------
        */

        $province = Region::firstOrCreate(
            [
                'code' => '32',
            ],
            [
                'parent_id' => null,
                'name' => 'Jawa Barat',
                'level' => 'province',
                'geometry' => null,
                'area_size' => null,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | KABUPATEN INDRAMAYU
        |--------------------------------------------------------------------------
        */

        $regency = Region::firstOrCreate(
            [
                'code' => '3212',
            ],
            [
                'parent_id' => $province->id,
                'name' => 'Kabupaten Indramayu',
                'level' => 'regency',
                'geometry' => null,
                'area_size' => null,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | KECAMATAN
        |--------------------------------------------------------------------------
        */

        $districts = [
            [
                'code' => '321201',
                'name' => 'Haurgeulis',
            ],
            [
                'code' => '321202',
                'name' => 'Gantar',
            ],
            [
                'code' => '321203',
                'name' => 'Kroya',
            ],
            [
                'code' => '321204',
                'name' => 'Gabuswetan',
            ],
            [
                'code' => '321205',
                'name' => 'Cikedung',
            ],
        ];


        foreach ($districts as $district) {

            Region::firstOrCreate(
                [
                    'code' => $district['code'],
                ],
                [
                    'parent_id' => $regency->id,
                    'name' => $district['name'],
                    'level' => 'district',
                    'geometry' => null,
                    'area_size' => null,
                ]
            );

        }
    }
}
