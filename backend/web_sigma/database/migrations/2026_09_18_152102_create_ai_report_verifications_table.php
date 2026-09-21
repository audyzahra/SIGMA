<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('ai_report_verifications', function (Blueprint $table) {


            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Relasi laporan masyarakat
            |--------------------------------------------------------------------------
            */

            $table->foreignId('citizen_report_id')
                ->constrained('citizen_reports')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Model AI yang digunakan
            |--------------------------------------------------------------------------
            */

            $table->foreignId('model_id')
                ->constrained('ai_models')
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Hasil prediksi AI
            |--------------------------------------------------------------------------
            */

            $table->enum('prediction_result',[
                'fire',
                'smoke',
                'no_fire',
                'uncertain'
            ]);



            /*
            |--------------------------------------------------------------------------
            | Confidence Score
            |--------------------------------------------------------------------------
            */

            $table->decimal('confidence_score',5,2)
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Objek hasil deteksi
            |--------------------------------------------------------------------------
            | Contoh:
            | {
            |   "fire":0.95,
            |   "smoke":0.80
            | }
            |--------------------------------------------------------------------------
            */

            $table->json('detected_objects')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Status verifikasi
            |--------------------------------------------------------------------------
            */

            $table->enum('verification_status',[
                'accepted',
                'reviewed',
                'rejected'
            ])
            ->default('reviewed');



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
        Schema::dropIfExists('ai_report_verifications');
    }

};
