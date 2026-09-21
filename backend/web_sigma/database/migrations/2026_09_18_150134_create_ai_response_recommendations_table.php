<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('ai_response_recommendations', function (Blueprint $table) {


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
            | Jenis rekomendasi AI
            |--------------------------------------------------------------------------
            */

            $table->enum('recommendation_type',[
                'deploy_team',
                'aerial_patrol',
                'evacuation',
                'community_alert'
            ]);



            /*
            |--------------------------------------------------------------------------
            | Detail rekomendasi
            |--------------------------------------------------------------------------
            */

            $table->text('recommendation_text');


            $table->text('risk_summary')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Nilai confidence model AI
            |--------------------------------------------------------------------------
            */

            $table->decimal('confidence_score',5,2)
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Versi model AI
            |--------------------------------------------------------------------------
            */

            $table->string('model_version',50)
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Status keputusan pemerintah
            |--------------------------------------------------------------------------
            */

            $table->enum('decision_status',[
                'pending',
                'accepted',
                'rejected'
            ])
            ->default('pending');



            /*
            |--------------------------------------------------------------------------
            | User pemerintah yang menyetujui
            |--------------------------------------------------------------------------
            */

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Waktu generate AI
            |--------------------------------------------------------------------------
            */

            $table->timestamp('generated_at')
                ->nullable();


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('ai_response_recommendations');
    }

};
