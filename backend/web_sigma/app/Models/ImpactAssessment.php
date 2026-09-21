<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpactAssessment extends Model
{

    protected $fillable = [
        'incident_id',
        'affected_population',
        'affected_households',
        'affected_area',
        'forest_area',
        'peatland_area',
        'school_count',
        'hospital_count',
        'road_distance',
        'impact_score',
        'calculated_at'
    ];


    protected $casts = [
        'calculated_at' => 'datetime',
    ];


    public function incident()
    {
        return $this->belongsTo(
            Incident::class
        );
    }

}
