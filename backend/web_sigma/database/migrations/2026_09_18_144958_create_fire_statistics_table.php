<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('fire_statistics', function (Blueprint $table) {


            $table->id();


            // Jumlah hotspot
            $table->integer('total_hotspots')
                ->default(0);


            // Kebakaran aktif
            $table->integer('active_incidents')
                ->default(0);


            // Kebakaran selesai
            $table->integer('resolved_incident')
                ->default(0);


            // Luas area terdampak
            $table->integer('affected_area')
                ->default(0);


            // Jumlah wilayah terdampak
            $table->integer('affected_region')
                ->default(0);


            // Tanggal statistik
            $table->date('statistic_date');


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('fire_statistics');
    }

};
