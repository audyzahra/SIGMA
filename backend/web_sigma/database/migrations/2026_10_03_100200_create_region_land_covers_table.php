<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Agregasi tutupan lahan per wilayah administratif (ESA WorldCover 2021)
|--------------------------------------------------------------------------
|
| Sumber: dataset/raw/land/ESA_WorldCover_10m_2021_v200_*.tif (~10 m,
| EPSG:4326) yang dijumlahkan per wilayah (zonal statistics).
|
| Kelas yang dipakai Analisis Dampak:
|   - 10 : Tree cover              -> Hutan
|   - 90 : Herbaceous wetland      -> Lahan basah / gambut
|
| `valid_ha` = luas daratan yang benar-benar terpetakan raster di dalam
| wilayah (piksel valid, bukan nodata). Dipakai sebagai penyebut fraksi
| tutupan sehingga wilayah yang hanya sebagian tercakup tile tidak
| menghasilkan fraksi yang menyesatkan.
|
| Catatan cakupan: 3 tile yang tersedia hanya mencakup bujur 120-123,
| lintang -6 s.d. 3 (Sulawesi/Maluku). Wilayah di luar cakupan tidak
| memiliki baris.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region_land_covers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('region_id')
                ->unique()
                ->constrained('regions')
                ->cascadeOnDelete();

            /* Luas daratan terpetakan (piksel valid) */
            $table->decimal('valid_ha', 16, 2)
                ->default(0);

            /* Luas hutan (kelas 10) dan lahan basah/gambut (kelas 90) */
            $table->decimal('forest_ha', 16, 2)
                ->default(0);

            $table->decimal('wetland_ha', 16, 2)
                ->default(0);

            /* Fraksi terhadap luas daratan terpetakan */
            $table->decimal('forest_percent', 7, 3)
                ->nullable();

            $table->decimal('wetland_percent', 7, 3)
                ->nullable();

            $table->string('source', 64)
                ->default('esa_worldcover_2021');

            $table->string('source_file')
                ->nullable();

            $table->timestamp('imported_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_land_covers');
    }
};
