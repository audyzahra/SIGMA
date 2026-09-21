<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('field_verifications', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Relasi incident
            |--------------------------------------------------------------------------
            */

            $table->foreignId('incident_id')
                ->constrained('incidents')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Tim yang melakukan verifikasi
            |--------------------------------------------------------------------------
            */

            $table->foreignId('team_id')
                ->constrained('field_teams')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Dokumentasi lapangan
            |--------------------------------------------------------------------------
            */

            $table->string('photo')
                ->nullable();


            $table->string('video')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Catatan petugas
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Waktu verifikasi
            |--------------------------------------------------------------------------
            */

            $table->timestamp('verified_at')
                ->nullable();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('field_verifications');
    }

};
