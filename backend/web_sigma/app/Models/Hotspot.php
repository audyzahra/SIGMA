<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotspot extends Model
{

    protected $fillable = [
        'source_id',
        'latitude',
        'longitude',
        'satellite_name',
        'brightness_temperature',
        'confidence_level',
        'detected_at',
        'status'
    ];


    protected $casts = [
        'detected_at'=>'datetime',
        'latitude'=>'decimal:8',
        'longitude'=>'decimal:8',
    ];


    public function source()
    {
        return $this->belongsTo(
            DataSource::class,
            'source_id'
        );
    }

}
