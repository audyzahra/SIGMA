<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamNavigationLog extends Model
{

    protected $fillable = [
        'assignment_id',
        'current_latitude',
        'current_longitude',
        'destination_latitude',
        'destination_longitude',
        'distance',
        'estimated_time'
    ];


    public function assignment()
    {
        return $this->belongsTo(
            FieldAssignment::class,
            'assignment_id'
        );
    }

}
