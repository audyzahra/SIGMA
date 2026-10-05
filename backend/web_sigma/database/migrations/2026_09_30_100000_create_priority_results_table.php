<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Tabel priority_results
|--------------------------------------------------------------------------
|
| Hasil Priority Calculation Engine: satu baris per wilayah (hasil terbaru).
|
| Ringkasan kolom:
|  - risk_score        : skor risiko wilayah  (fire_risks, hasil AI Service)
|  - impact_score      : skor dampak wilayah  (impact_assessments, analisis GIS)
|  - priority_score    : (risk x bobot risk) + (impact x bobot impact)
|  - priority_level    : critical | high | medium | low
|  - ranking_position  : peringkat nasional hasil perhitungan
|  - data_completeness : persentase bobot yang datanya tersedia (transparansi)
|  - components        : rincian komponen skor & bobot efektif per komponen
|  - sources           : snapshot sumber data yang dipakai saat perhitungan
|
| Kolom skor NULL berarti "belum tersedia" (bukan nol). Wilayah tanpa data
| sama sekali tidak memiliki baris di tabel ini.
|
| Unik pada region_id karena modul menyimpan hasil perhitungan TERBARU.
| Riwayat perhitungan risiko tetap tersimpan di fire_risk_histories.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priority_results', function (Blueprint $table) {

            $table->id();

            $table->foreignId('region_id')
                ->unique()
                ->constrained('regions')
                ->cascadeOnDelete();

            $table->decimal('risk_score', 5, 2)
                ->nullable();

            $table->decimal('impact_score', 5, 2)
                ->nullable();

            $table->decimal('priority_score', 5, 2)
                ->nullable();

            $table->enum('priority_level', [
                'critical',
                'high',
                'medium',
                'low',
            ])->nullable();

            $table->unsignedInteger('ranking_position')
                ->nullable();

            $table->decimal('data_completeness', 5, 2)
                ->default(0);

            $table->json('components')
                ->nullable();

            $table->json('sources')
                ->nullable();

            $table->string('methodology_version', 32)
                ->nullable();

            /* Waktu sumber data (untuk jejak audit perhitungan) */
            $table->timestamp('risk_calculated_at')
                ->nullable();

            $table->timestamp('impact_calculated_at')
                ->nullable();

            $table->timestamp('calculated_at')
                ->nullable();

            $table->timestamps();

            $table->index('priority_score', 'priority_results_score_index');
            $table->index('priority_level', 'priority_results_level_index');
            $table->index(['priority_level', 'priority_score'], 'priority_results_level_score_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priority_results');
    }
};
