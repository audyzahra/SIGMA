<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fire_risk_histories', function (Blueprint $table) {

            $table->id();

            // Relasi wilayah
            $table->foreignId('region_id')
                ->constrained('regions')
                ->cascadeOnDelete();

            // Nilai risiko 0-100
            $table->integer('risk_score');

            // Level risiko
            $table->enum('risk_level', [
                'low',
                'medium',
                'high',
                'extreme'
            ]);

            // Waktu perhitungan risiko
            $table->timestamp('calculated_at');

            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('fire_risk_histories');
    }
};
