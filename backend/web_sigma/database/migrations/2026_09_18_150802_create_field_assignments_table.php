<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('field_assignments', function (Blueprint $table) {


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
            | Tim penyelamat
            |--------------------------------------------------------------------------
            */

            $table->foreignId('team_id')
                ->constrained('field_teams')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Pemerintah pemberi tugas
            |--------------------------------------------------------------------------
            */

            $table->foreignId('assigned_by')
                ->constrained('users')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Prioritas tugas
            |--------------------------------------------------------------------------
            */

            $table->enum('priority_level',[
                'low',
                'medium',
                'high',
                'extreme'
            ])
            ->default('medium');



            /*
            |--------------------------------------------------------------------------
            | Status pekerjaan lapangan
            |--------------------------------------------------------------------------
            */

            $table->enum('status',[
                'assigned',
                'traveling',
                'arrived',
                'handling',
                'completed'
            ])
            ->default('assigned');



            /*
            |--------------------------------------------------------------------------
            | Waktu proses
            |--------------------------------------------------------------------------
            */

            $table->timestamp('assigned_at')
                ->nullable();


            $table->timestamp('completed_at')
                ->nullable();



            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('field_assignments');
    }

};
