<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('incidents_status_histories', function (Blueprint $table) {


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
            | Petugas yang melakukan update
            |--------------------------------------------------------------------------
            */

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Status incident
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'detected',
                'on_process',
                'extinguished'
            ]);



            /*
            |--------------------------------------------------------------------------
            | Catatan petugas
            |--------------------------------------------------------------------------
            */

            $table->text('note')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Bukti foto lapangan
            |--------------------------------------------------------------------------
            */

            $table->string('evidence_photo')
                ->nullable();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('incidents_status_histories');
    }

};
