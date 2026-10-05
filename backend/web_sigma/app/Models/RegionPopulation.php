<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Agregasi jumlah penduduk per wilayah administratif.
 *
 * Sumber: WorldPop UN-adjusted Population Counts 2020 (raster ~100 m) yang
 * dijumlahkan per kecamatan/kabupaten/provinsi. Dipakai Analisis Dampak
 * untuk menghitung "Penduduk Terpapar" di dalam radius analisis.
 */
class RegionPopulation extends Model
{
    protected $fillable = [
        'region_id',
        'population',
        'area_km2',
        'population_density',
        'grid_cells',
        'source',
        'source_file',
        'imported_at',
    ];

    protected $casts = [
        'population' => 'integer',
        'area_km2' => 'float',
        'population_density' => 'float',
        'grid_cells' => 'integer',
        'imported_at' => 'datetime',
    ];

    /**
     * Wilayah administratif yang dihitung populasinya.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
