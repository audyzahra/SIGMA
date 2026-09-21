<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldVerification extends Model
{

    protected $fillable = [
        'incident_id',
        'team_id',
        'photo',
        'video',
        'description',
        'verified_at'
    ];


    protected $casts = [
        'verified_at'=>'datetime'
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

}
