<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseAssignment extends Model
{

    protected $fillable = [
        'incident_id',
        'team_id',
        'assigned_by',
        'status',
        'assigned_at'
    ];


    protected $casts = [
        'assigned_at'=>'datetime'
    ];



    public function incident()
    {
        return $this->belongsTo(
            Incident::class
        );
    }


    public function team()
    {
        return $this->belongsTo(
            FieldTeam::class,
            'team_id'
        );
    }


    public function assigner()
    {
        return $this->belongsTo(
            User::class,
            'assigned_by'
        );
    }

}
