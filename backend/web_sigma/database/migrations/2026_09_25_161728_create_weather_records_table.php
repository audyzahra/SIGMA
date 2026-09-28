<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {
        Schema::create('weather_records', function (Blueprint $table) {


            $table->id();



            // wilayah jika nanti ingin per kabupaten

            $table->foreignId('region_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();



            // koordinat sumber data

            $table->decimal(
                'latitude',
                10,
                8
            );


            $table->decimal(
                'longitude',
                11,
                8
            );



            // NASA POWER

            $table->date(
                'recorded_date'
            );



            $table->decimal(
                'temperature',
                8,
                2
            )
            ->nullable();



            $table->decimal(
                'humidity',
                8,
                2
            )
            ->nullable();



            $table->decimal(
                'rainfall',
                8,
                2
            )
            ->nullable();



            $table->decimal(
                'wind_speed',
                8,
                2
            )
            ->nullable();



            $table->decimal(
                'solar_radiation',
                8,
                2
            )
            ->nullable();



            $table->timestamps();

        });
    }



    public function down(): void
    {
        Schema::dropIfExists('weather_records');
    }

};
