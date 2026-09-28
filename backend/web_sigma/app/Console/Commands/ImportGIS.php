<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GIS\GeoJsonImporter;


class ImportGIS extends Command
{

    protected $signature = 'gis:import';


    protected $description =
        'Import GIS Indonesia sampai tingkat Kecamatan';



    public function handle(
        GeoJsonImporter $importer
    )
    {


        $this->info(
            '================================='
        );


        $this->info(
            'START IMPORT GIS INDONESIA'
        );


        $this->info(
            '================================='
        );



        $this->info(
            'Import Province...'
        );


        $importer->importProvince();



        $this->info(
            'Import Regency...'
        );


        $importer->importRegency();



        $this->info(
            'Import District...'
        );


        $importer->importDistrict();



        $this->info(
            '================================='
        );


        $this->info(
            'GIS IMPORT SELESAI'
        );


        $this->info(
            'Level tersedia: Province, Regency, District'
        );


        $this->info(
            '================================='
        );


        return Command::SUCCESS;


    }


}
