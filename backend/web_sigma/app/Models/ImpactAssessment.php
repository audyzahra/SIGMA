<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpactAssessment extends Model
{

    protected $fillable = [

        /* poros analisis (salah satu boleh terisi) */
        'incident_id',
        'region_id',

        /* parameter analisis spasial */
        'analysis_radius_km',
        'center_latitude',
        'center_longitude',
        'center_source',

        /* dampak area (hasil perhitungan GIS, km2) */
        'affected_area',

        /* objek terdampak di dalam radius */
        'affected_province_count',
        'affected_regency_count',
        'affected_district_count',
        'hotspot_count',
        'incident_count',
        'report_count',

        /* konteks risiko wilayah terdampak (bukan skor dampak) */
        'risk_weighted_score',

        /*
         * Kolom dampak sosial/fasilitas.
         * NULL = belum terukur (dataset belum tersedia), bukan 0.
         */
        'affected_population',
        'affected_households',
        'forest_area',
        'peatland_area',
        'school_count',
        'hospital_count',
        'road_distance',

        /* hasil analisis */
        'impact_score',
        'impact_level',
        'methodology_version',
        'components',
        'exposure',
        'data_sources',

        'calculated_at',
    ];


    protected $casts = [
        'calculated_at' => 'datetime',
        'analysis_radius_km' => 'float',
        'center_latitude' => 'float',
        'center_longitude' => 'float',
        'affected_area' => 'float',
        'risk_weighted_score' => 'float',
        'components' => 'array',
        'exposure' => 'array',
        'data_sources' => 'array',
    ];


    public function incident()
    {
        return $this->belongsTo(
            Incident::class
        );
    }


    public function region()
    {
        return $this->belongsTo(
            Region::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    /**
     * Analisis berbasis wilayah (tanpa insiden) pada radius tertentu.
     */
    public function scopeForRegion($query, int $regionId, ?float $radiusKm = null)
    {
        $query = $query->where('region_id', $regionId)
            ->whereNull('incident_id');

        if ($radiusKm !== null) {
            $query->where('analysis_radius_km', $radiusKm);
        }

        return $query;
    }


    /**
     * Analisis dengan skor tertinggi lebih dulu.
     */
    public function scopeHighImpactFirst($query)
    {
        return $query->orderByDesc('impact_score')
            ->orderByDesc('calculated_at');
    }


    /**
     * Cek apakah kolom dampak sosial masih "belum terukur" (null).
     */
    public function hasUnmeasuredImpactFields(): bool
    {
        return $this->affected_population === null
            || $this->affected_households === null
            || $this->school_count === null
            || $this->hospital_count === null;
    }

}
