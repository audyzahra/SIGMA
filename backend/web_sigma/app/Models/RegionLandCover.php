<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Agregasi tutupan lahan per wilayah administratif (ESA WorldCover 2021).
 *
 * Dipakai Analisis Dampak untuk menghitung Luas Hutan Terdampak
 * (kelas 10) dan Luas Lahan Gambut/Lahan Basah Terdampak (kelas 90)
 * di dalam zona analisis.
 */
class RegionLandCover extends Model
{
    protected $fillable = [
        'region_id',
        'valid_ha',
        'forest_ha',
        'wetland_ha',
        'forest_percent',
        'wetland_percent',
        'source',
        'source_file',
        'imported_at',
    ];

    protected $casts = [
        'valid_ha' => 'float',
        'forest_ha' => 'float',
        'wetland_ha' => 'float',
        'forest_percent' => 'float',
        'wetland_percent' => 'float',
        'imported_at' => 'datetime',
    ];

    /**
     * Wilayah administratif yang diukur tutupan lahannya.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
