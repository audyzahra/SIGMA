<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Agregasi penduduk per wilayah administratif
|--------------------------------------------------------------------------
|
| Sumber: dataset/raw/population/idn_ppp_2020_UNadj.tif
| (WorldPop UN-adjusted Population Counts 2020, resolusi ~100 m, EPSG:4326).
|
| Raster penduduk dijumlahkan (zonal statistics) per wilayah, terutama
| tingkat KECAMATAN (district) karena level inilah yang dipakai Analisis
| Dampak untuk menghitung "Penduduk Terpapar":
|
|   Penduduk Terpapar = Σ ( populasi_kecamatan
|                           × luas_irisan_kecamatan
|                           / luas_kecamatan            )
|
| Baris untuk kabupaten/provinsi juga disimpan (hasil penjumlahan
| kecamatan) supaya level lain dapat dipakai tanpa menghitung ulang raster.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region_populations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('region_id')
                ->unique()
                ->constrained('regions')
                ->cascadeOnDelete();

            /* Jumlah penduduk (jiwa) pada wilayah tersebut */
            $table->unsignedBigInteger('population')
                ->default(0);

            /* Luas wilayah (km2) dan kepadatan (jiwa/km2) */
            $table->decimal('area_km2', 14, 2)
                ->nullable();

            $table->decimal('population_density', 14, 3)
                ->nullable();

            /* Jumlah sel raster (piksel valid) yang dijumlahkan */
            $table->unsignedInteger('grid_cells')
                ->nullable();

            $table->string('source', 64)
                ->default('worldpop_ppp_2020_unadj');

            $table->string('source_file')
                ->nullable();

            $table->timestamp('imported_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_populations');
    }
};
