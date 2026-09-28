<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class WeatherRecord extends Model
{


    protected $fillable = [

        'region_id',

        'latitude',

        'longitude',

        'recorded_date',

        'temperature',

        'humidity',

        'rainfall',

        'wind_speed',

        'solar_radiation',

    ];



    protected $casts = [

        'recorded_date'=>'date',

    ];



    public function region()
    {

        return $this->belongsTo(
            Region::class
        );

    }

}
