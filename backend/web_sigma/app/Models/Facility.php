<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Fasilitas publik hasil ingestion dataset spasial (OpenStreetMap/HOTOSM).
 *
 * Dipakai Analisis Dampak untuk menghitung Sekolah Terdampak
 * (category = education) dan Fasilitas Kesehatan Terdampak
 * (category = health) di dalam radius analisis.
 *
 * Kolom geometri `location` (POINT SRID 4326) tidak masuk $fillable
 * karena penulisannya dilakukan lewat SQL spasial (ST_SRID(POINT(...),4326)).
 */
class Facility extends Model
{
    public const CATEGORY_EDUCATION = 'education';

    public const CATEGORY_HEALTH = 'health';

    protected $fillable = [
        'region_id',
        'category',
        'facility_type',
        'name',
        'osm_id',
        'osm_type',
        'latitude',
        'longitude',
        'source',
        'source_file',
        'properties',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'osm_id' => 'integer',
        'properties' => 'array',
    ];

    /**
     * Wilayah administratif tempat fasilitas berada (bila terdeteksi).
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Filter berdasarkan kategori fasilitas (education, health, ...).
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
