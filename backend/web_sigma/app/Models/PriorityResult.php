<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Hasil Priority Calculation Engine untuk satu wilayah.
 *
 * Relasi utama:
 *   Region
 *     |-- FireRisk         (risk_score, hasil AI Service)
 *     |-- ImpactAssessment (impact_score, hasil analisis spasial GIS)
 *     |-- PriorityResult   (priority_score, priority_level, ranking)
 *
 * Nama wilayah TIDAK disimpan di tabel ini; selalu dibaca lewat relasi
 * region supaya tidak ada duplikasi data administratif.
 */
class PriorityResult extends Model
{
    protected $fillable = [
        'region_id',
        'risk_score',
        'impact_score',
        'priority_score',
        'priority_level',
        'ranking_position',
        'data_completeness',
        'components',
        'sources',
        'methodology_version',
        'risk_calculated_at',
        'impact_calculated_at',
        'calculated_at',
    ];

    protected $casts = [
        'risk_score' => 'float',
        'impact_score' => 'float',
        'priority_score' => 'float',
        'data_completeness' => 'float',
        'ranking_position' => 'integer',
        'components' => 'array',
        'sources' => 'array',
        'risk_calculated_at' => 'datetime',
        'impact_calculated_at' => 'datetime',
        'calculated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    /**
     * Urutan ranking: skor prioritas tertinggi lebih dulu.
     */
    public function scopeRanked($query)
    {
        return $query->orderByDesc('priority_score')
            ->orderBy('ranking_position');
    }

    /**
     * Saring berdasarkan kategori prioritas (critical|high|medium|low).
     */
    public function scopeForLevel($query, ?string $level)
    {
        return $level ? $query->where('priority_level', $level) : $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Bantuan status data
    |--------------------------------------------------------------------------
    */

    public function hasRiskScore(): bool
    {
        return $this->risk_score !== null;
    }

    public function hasImpactScore(): bool
    {
        return $this->impact_score !== null;
    }

    /**
     * Apakah Risk Score dan Impact Score dua-duanya tersedia?
     */
    public function hasCompleteData(): bool
    {
        return $this->hasRiskScore() && $this->hasImpactScore();
    }
}
