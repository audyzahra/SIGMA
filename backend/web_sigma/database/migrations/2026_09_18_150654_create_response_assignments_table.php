<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('response_assignments', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Relasi insiden
            |--------------------------------------------------------------------------
            */

            $table->foreignId('incident_id')
                ->constrained('incidents')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Tim penyelamat
            |--------------------------------------------------------------------------
            */

            $table->foreignId('team_id')
                ->constrained('field_teams')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | User pemerintah yang memberi tugas
            |--------------------------------------------------------------------------
            */

            $table->foreignId('assigned_by')
                ->constrained('users')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Status penugasan
            |--------------------------------------------------------------------------
            */

            $table->enum('status',[
                'assigned',
                'progress',
                'completed'
            ])
            ->default('assigned');



            /*
            |--------------------------------------------------------------------------
            | Waktu penugasan
            |--------------------------------------------------------------------------
            */

            $table->timestamp('assigned_at')
                ->nullable();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('response_assignments');
    }

};
