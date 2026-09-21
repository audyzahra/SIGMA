<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('team_navigation_logs', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Relasi penugasan tim
            |--------------------------------------------------------------------------
            */

            $table->foreignId('assignment_id')
                ->constrained('field_assignments')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Posisi terakhir tim
            |--------------------------------------------------------------------------
            */

            $table->decimal('current_latitude',10,8);

            $table->decimal('current_longitude',11,8);



            /*
            |--------------------------------------------------------------------------
            | Tujuan lokasi insiden
            |--------------------------------------------------------------------------
            */

            $table->decimal('destination_latitude',10,8);

            $table->decimal('destination_longitude',11,8);



            /*
            |--------------------------------------------------------------------------
            | Informasi perjalanan
            |--------------------------------------------------------------------------
            */

            $table->decimal('distance',10,2)
                ->nullable();


            // dalam menit
            $table->integer('estimated_time')
                ->nullable();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('team_navigation_logs');
    }

};
