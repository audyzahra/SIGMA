<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FireRisk extends Model
{

    protected $fillable = [
        'region_id',
        'risk_score',
        'risk_level',
        'temperature',
        'humidity',
        'rainfall',
        'wind_speed',
        'vegetation_index',
        'calculated_at'
    ];


    protected $casts = [
        'calculated_at'=>'datetime',
    ];


    public function region()
    {
        return $this->belongsTo(
            Region::class
        );
    }


    public function histories()
    {
        return $this->hasMany(
            FireRiskHistory::class
        );
    }

}
