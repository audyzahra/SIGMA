<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{

    protected $fillable = [
        'name',
        'type',
        'address',
        'phone',
        'email',
        'region_id',
        'status'
    ];


    public function region()
    {
        return $this->belongsTo(
            Region::class
        );
    }


    public function users()
    {
        return $this->hasMany(
            User::class
        );
    }


    public function fieldTeams()
    {
        return $this->hasMany(
            FieldTeam::class
        );
    }

}
