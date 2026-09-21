<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('incident_priorities', function (Blueprint $table) {


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
            | Nilai perhitungan AI
            |--------------------------------------------------------------------------
            */

            $table->integer('risk_score')
                ->default(0);


            $table->integer('impact_score')
                ->default(0);


            $table->integer('priority_score')
                ->default(0);



            /*
            |--------------------------------------------------------------------------
            | Level prioritas
            |--------------------------------------------------------------------------
            */

            $table->enum('priority_level',[
                'low',
                'medium',
                'high',
                'critical'
            ])
            ->default('low');



            /*
            |--------------------------------------------------------------------------
            | Ranking posisi
            |--------------------------------------------------------------------------
            */

            $table->integer('ranking_position')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Sumber generate
            |--------------------------------------------------------------------------
            */

            $table->enum('generated_by',[
                'AI',
                'manual'
            ])
            ->default('AI');


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('incident_priorities');
    }

};
