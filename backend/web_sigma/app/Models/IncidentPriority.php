<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentPriority extends Model
{

    protected $fillable = [
        'incident_id',
        'risk_score',
        'impact_score',
        'priority_score',
        'priority_level',
        'ranking_position',
        'generated_by'
    ];


    public function incident()
    {
        return $this->belongsTo(
            Incident::class
        );
    }

}
