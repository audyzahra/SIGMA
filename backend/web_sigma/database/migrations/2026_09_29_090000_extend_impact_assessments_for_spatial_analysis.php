<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Perluas impact_assessments untuk analisis dampak berbasis spasial
|--------------------------------------------------------------------------
|
| Alasan:
|  1. Analisis dampak SIGMA dapat berporos pada INSIDEN, WILAYAH, HOTSPOT,
|     atau KOORDINAT. Tabel lama hanya mendukung insiden (incident_id NOT NULL).
|  2. Hasil analisis spasial (radius, titik pusat, luas irisan wilayah,
|     jumlah hotspot, rincian komponen skor, sumber data) perlu disimpan
|     agar dapat diaudit dan tidak dihitung ulang setiap request.
|  3. Kolom dampak lama (affected_population, affected_households,
|     school_count, hospital_count, forest_area, peatland_area) diubah
|     menjadi NULLABLE karena dataset penduduk/permukiman/fasilitas/jalan/
|     tutupan lahan BELUM tersedia di SIGMA. Nilai 0 berpotensi
|     disalahartikan sebagai "tidak ada dampak", padahal artinya "belum diukur".
|
| Migrasi ini additive & reversible: kolom lama tetap ada.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impact_assessments', function (Blueprint $table) {

            /* Analisis dapat berporos pada wilayah (bukan hanya insiden) */
            $table->foreignId('region_id')
                ->nullable()
                ->after('incident_id')
                ->constrained('regions')
                ->nullOnDelete();

            /* Parameter analisis spasial */
            $table->decimal('analysis_radius_km', 6, 2)
                ->nullable()
                ->after('region_id');

            $table->decimal('center_latitude', 10, 7)
                ->nullable()
                ->after('analysis_radius_km');

            $table->decimal('center_longitude', 10, 7)
                ->nullable()
                ->after('center_latitude');

            $table->string('center_source', 32)
                ->nullable()
                ->after('center_longitude');

            /* Cakupan wilayah terdampak (ST_Intersects + ST_Intersection) */
            $table->unsignedInteger('affected_province_count')
                ->nullable()
                ->after('affected_area');

            $table->unsignedInteger('affected_regency_count')
                ->nullable()
                ->after('affected_province_count');

            $table->unsignedInteger('affected_district_count')
                ->nullable()
                ->after('affected_regency_count');

            /* Objek di dalam radius */
            $table->unsignedInteger('hotspot_count')
                ->nullable()
                ->after('affected_district_count');

            $table->unsignedInteger('incident_count')
                ->nullable()
                ->after('hotspot_count');

            $table->unsignedInteger('report_count')
                ->nullable()
                ->after('incident_count');

            /* Konteks risiko wilayah terdampak (bukan skor dampak) */
            $table->decimal('risk_weighted_score', 5, 2)
                ->nullable()
                ->after('report_count');

            /* Hasil analisis */
            $table->string('impact_level', 16)
                ->nullable()
                ->after('impact_score');

            $table->string('methodology_version', 32)
                ->nullable()
                ->after('impact_level');

            $table->json('components')
                ->nullable()
                ->after('methodology_version');

            $table->json('exposure')
                ->nullable()
                ->after('components');

            $table->json('data_sources')
                ->nullable()
                ->after('exposure');
        });

        Schema::table('impact_assessments', function (Blueprint $table) {
            $table->index(['region_id', 'analysis_radius_km'], 'impact_assessments_region_radius_index');
            $table->index('impact_level', 'impact_assessments_level_index');
        });

        /* Insiden menjadi opsional: analisis bisa murni berbasis wilayah */
        Schema::table('impact_assessments', function (Blueprint $table) {
            $table->foreignId('incident_id')->nullable()->change();
        });

        /*
         * Kolom dampak lama menjadi nullable: artinya "belum terukur" dan
         * bukan "nol" (dataset penduduk/permukiman/fasilitas belum tersedia).
         */
        Schema::table('impact_assessments', function (Blueprint $table) {
            $table->integer('affected_population')->nullable()->default(null)->change();
            $table->integer('affected_households')->nullable()->default(null)->change();
            $table->decimal('forest_area', 12, 2)->nullable()->default(null)->change();
            $table->decimal('peatland_area', 12, 2)->nullable()->default(null)->change();
            $table->integer('school_count')->nullable()->default(null)->change();
            $table->integer('hospital_count')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('impact_assessments', function (Blueprint $table) {
            $table->integer('affected_population')->nullable(false)->default(0)->change();
            $table->integer('affected_households')->nullable(false)->default(0)->change();
            $table->decimal('forest_area', 12, 2)->nullable(false)->default(0)->change();
            $table->decimal('peatland_area', 12, 2)->nullable(false)->default(0)->change();
            $table->integer('school_count')->nullable(false)->default(0)->change();
            $table->integer('hospital_count')->nullable(false)->default(0)->change();
        });

        Schema::table('impact_assessments', function (Blueprint $table) {
            $table->dropIndex('impact_assessments_level_index');
            $table->dropIndex('impact_assessments_region_radius_index');
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::table('impact_assessments', function (Blueprint $table) {
            $table->dropColumn([
                'analysis_radius_km',
                'center_latitude',
                'center_longitude',
                'center_source',
                'affected_province_count',
                'affected_regency_count',
                'affected_district_count',
                'hotspot_count',
                'incident_count',
                'report_count',
                'risk_weighted_score',
                'impact_level',
                'methodology_version',
                'components',
                'exposure',
                'data_sources',
            ]);
        });
    }
};