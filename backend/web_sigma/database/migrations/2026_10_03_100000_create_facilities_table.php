<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Tabel fasilitas publik (hasil ingestion dataset spasial)
|--------------------------------------------------------------------------
|
| Sumber: dataset/raw/facilities/[wilayah]/SIGMA_Fasilitas_Umum_*.geojson
| (ekspor OpenStreetMap melalui HOTOSM raw-data-api, lisensi ODbL).
|
| Dipakai Analisis Dampak untuk menghitung:
|   - Sekolah Terdampak           (category = education)
|   - Fasilitas Kesehatan Dampak  (category = health)
|
| Kolom `location` bertipe POINT dengan SRID 4326 dan diberi SPATIAL INDEX
| sehingga pencarian fasilitas di dalam radius analisis memakai
| ST_Distance_Sphere / ST_Intersects memakai index (bukan full scan).
| InnoDB mensyaratkan kolom ber-spatial index bersifat NOT NULL, maka
| `location` dibuat NOT NULL (baris tanpa koordinat tidak diimpor).
|
| `region_id` bersifat best-effort (diisi bila titik berada dalam wilayah)
| dan tidak dipakai untuk perhitungan jarak — perhitungan selalu geometrik.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {

            $table->id();

            /* Wilayah administratif (best-effort, boleh null) */
            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            /*
             | Klasifikasi fasilitas
             | category      : education | health | government | worship |
             |                 transport | market | emergency | community |
             |                 sport | other
             | facility_type : nilai spesifik OSM (school, hospital, clinic, ...)
             */
            $table->string('category', 32)
                ->index();

            $table->string('facility_type', 64)
                ->nullable();

            $table->string('name')
                ->nullable();

            /* Identitas sumber (OpenStreetMap) */
            $table->unsignedBigInteger('osm_id')
                ->nullable();

            $table->string('osm_type', 16)
                ->nullable();

            /* Koordinat (memudahkan tampilan & debugging) */
            $table->decimal('latitude', 10, 7)
                ->nullable();

            $table->decimal('longitude', 10, 7)
                ->nullable();

            /* Geometri titik SRID 4326 untuk query radius */
            $table->geometry('location');

            $table->string('source', 64)
                ->default('osm_hotosm');

            $table->string('source_file')
                ->nullable();

            /* Atribut asli OSM (disimpan untuk audit & klasifikasi ulang) */
            $table->json('properties')
                ->nullable();

            $table->timestamps();

            /* Satu OSM feature hanya diimpor sekali */
            $table->unique(['osm_type', 'osm_id'], 'facilities_osm_unique');
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->spatialIndex('location', 'facilities_location_spatial_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
