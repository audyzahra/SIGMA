<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldAssignment extends Model
{

    protected $fillable = [
        'incident_id',
        'team_id',
        'assigned_by',
        'priority_level',
        'status',
        'assigned_at',
        'completed_at'
    ];


    protected $casts = [
        'assigned_at'=>'datetime',
        'completed_at'=>'datetime'
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


    public function navigationLogs()
    {
        return $this->hasMany(
            TeamNavigationLog::class,
            'assignment_id'
        );
    }

}
