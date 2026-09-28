<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ResponseAction;


class ResponseActionSeeder extends Seeder
{

    public function run(): void
    {

        $data = [

            'Pemadaman Awal',
            'Evakuasi',
            'Patroli Area',
            'Monitoring Risiko',
            'Cek Sumber Air',
            'Peringatan Masyarakat',

        ];


        foreach ($data as $item) {


            ResponseAction::updateOrCreate(

                [
                    'name' => $item
                ],

                [
                    'is_active' => true
                ]

            );


        }

    }

}