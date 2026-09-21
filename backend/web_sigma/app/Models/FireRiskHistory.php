<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FireRiskHistory extends Model
{

    protected $fillable = [
        'region_id',
        'risk_score',
        'risk_level',
        'calculated_at'
    ];


    protected $casts = [
        'calculated_at'=>'datetime'
    ];


    public function region()
    {
        return $this->belongsTo(
            Region::class
        );
    }

}
