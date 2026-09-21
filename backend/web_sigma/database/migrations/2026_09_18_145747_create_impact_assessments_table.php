<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('impact_assessments', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Relasi kejadian kebakaran
            |--------------------------------------------------------------------------
            */

            $table->foreignId('incident_id')
                ->constrained('incidents')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Dampak populasi
            |--------------------------------------------------------------------------
            */

            $table->integer('affected_population')
                ->default(0);


            $table->integer('affected_households')
                ->default(0);



            /*
            |--------------------------------------------------------------------------
            | Dampak area
            |--------------------------------------------------------------------------
            */

            $table->decimal('affected_area',12,2)
                ->default(0);


            $table->decimal('forest_area',12,2)
                ->default(0);


            $table->decimal('peatland_area',12,2)
                ->default(0);



            /*
            |--------------------------------------------------------------------------
            | Fasilitas terdampak
            |--------------------------------------------------------------------------
            */

            $table->integer('school_count')
                ->default(0);


            $table->integer('hospital_count')
                ->default(0);



            /*
            |--------------------------------------------------------------------------
            | Jarak akses
            |--------------------------------------------------------------------------
            */

            $table->decimal('road_distance',10,2)
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Nilai dampak AI
            |--------------------------------------------------------------------------
            */

            $table->integer('impact_score')
                ->default(0);



            /*
            |--------------------------------------------------------------------------
            | Waktu kalkulasi
            |--------------------------------------------------------------------------
            */

            $table->timestamp('calculated_at')
                ->nullable();


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('impact_assessments');
    }

};
