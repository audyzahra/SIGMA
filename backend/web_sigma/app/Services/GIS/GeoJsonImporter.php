<?php

namespace App\Services\GIS;

use App\Models\Region;
use Illuminate\Support\Facades\DB;


class GeoJsonImporter
{


    private function loadJson($file)
    {

        $path = storage_path(
            'app/gis/'.$file
        );


        if(!file_exists($path))
        {

            echo "File tidak ditemukan : ".$file."\n";

            return null;

        }


        $json = json_decode(
            file_get_contents($path),
            true
        );


        if(!isset($json['features']))
        {

            echo "Format JSON salah : ".$file."\n";

            return null;

        }


        return $json;


    }




    private function saveGeometry(
        $region,
        $geometry
    )
    {


        if(!$geometry)
        {
            return;
        }


        DB::statement(

            "
            UPDATE regions

            SET geometry = ST_GeomFromGeoJSON(?)

            WHERE id = ?

            ",

            [

                json_encode($geometry),

                $region->id

            ]

        );


    }






    public function importProvince()
    {


        echo "Import Province...\n";


        $json = $this->loadJson(
            'gadm41_IDN_1.json'
        );


        if(!$json)
        {
            return;
        }



        foreach($json['features'] as $feature)
        {


            $p = $feature['properties'];



            $region = Region::updateOrCreate(

                [

                    'code'=>$p['GID_1']

                ],


                [

                    'parent_id'=>null,

                    'name'=>str_replace(
                        '_',
                        ' ',
                        $p['NAME_1']
                    ),

                    'level'=>'province'

                ]

            );



            $this->saveGeometry(
                $region,
                $feature['geometry']
            );


        }



        echo "Province selesai\n";


    }









    public function importRegency()
    {


        echo "Import Regency...\n";


        $json = $this->loadJson(
            'gadm41_IDN_2.json'
        );


        if(!$json)
        {
            return;
        }



        foreach($json['features'] as $feature)
        {


            $p = $feature['properties'];



            $province = Region::where(
                'code',
                $p['GID_1']
            )
            ->where(
                'level',
                'province'
            )
            ->first();



            if(!$province)
            {
                continue;
            }



            $region = Region::updateOrCreate(

                [

                    'code'=>$p['GID_2']

                ],


                [

                    'parent_id'=>$province->id,

                    'name'=>str_replace(
                        '_',
                        ' ',
                        $p['NAME_2']
                    ),

                    'level'=>'regency'

                ]

            );



            $this->saveGeometry(
                $region,
                $feature['geometry']
            );


        }



        echo "Regency selesai\n";


    }









    public function importDistrict()
    {


        echo "Import District...\n";


        $json = $this->loadJson(
            'gadm41_IDN_3.json'
        );


        if(!$json)
        {
            return;
        }



        foreach($json['features'] as $feature)
        {


            $p = $feature['properties'];



            $regency = Region::where(
                'code',
                $p['GID_2']
            )
            ->where(
                'level',
                'regency'
            )
            ->first();



            if(!$regency)
            {
                continue;
            }




            $region = Region::updateOrCreate(

                [

                    'code'=>$p['GID_3']

                ],


                [

                    'parent_id'=>$regency->id,

                    'name'=>str_replace(
                        '_',
                        ' ',
                        $p['NAME_3']
                    ),

                    'level'=>'district'

                ]

            );



            $this->saveGeometry(
                $region,
                $feature['geometry']
            );


        }


        echo "District selesai\n";


    }

}
