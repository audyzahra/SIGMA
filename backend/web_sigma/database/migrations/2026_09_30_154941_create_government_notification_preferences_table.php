<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('government_notification_preferences', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Risiko Karhutla
            |--------------------------------------------------------------------------
            */

            $table->boolean('risk_alert')
                ->default(true);

            $table->boolean('critical_risk_alert')
                ->default(true);


            /*
            |--------------------------------------------------------------------------
            | Laporan Masyarakat
            |--------------------------------------------------------------------------
            */

            $table->boolean('citizen_report_alert')
                ->default(true);


            /*
            |--------------------------------------------------------------------------
            | Tim Lapangan
            |--------------------------------------------------------------------------
            */

            $table->boolean('field_team_alert')
                ->default(true);


            /*
            |--------------------------------------------------------------------------
            | AI SIGMA
            |--------------------------------------------------------------------------
            */

            $table->boolean('ai_recommendation_alert')
                ->default(true);


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'government_notification_preferences'
        );
    }
};
