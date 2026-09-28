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

        'frp',

        'detected_at',

        'status'

    ];



    protected $casts = [

        'detected_at'=>'datetime',

        'latitude'=>'float',

        'longitude'=>'float',

        'brightness_temperature'=>'float',

        'frp'=>'float',

    ];




    public function source()
    {

        return $this->belongsTo(

            DataSource::class,

            'source_id'

        );

    }


}
