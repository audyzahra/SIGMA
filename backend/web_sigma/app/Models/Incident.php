<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{

    protected $fillable = [
        'source_type',
        'latitude',
        'longitude',
        'location_description',
        'fire_status',
        'severity_level',
        'photo',
        'detected_at'
    ];


    protected $casts = [
        'detected_at' => 'datetime'
    ];


    /*
    |--------------------------------------------------------------------------
    | Command Center Government
    |--------------------------------------------------------------------------
    */

    public function impactAssessment()
    {
        return $this->hasOne(
            ImpactAssessment::class
        );
    }


    public function priority()
    {
        return $this->hasOne(
            IncidentPriority::class
        );
    }


    public function recommendations()
    {
        return $this->hasMany(
            AiResponseRecommendation::class
        );
    }


    public function responseAssignments()
    {
        return $this->hasMany(
            ResponseAssignment::class
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Citizen Report
    |--------------------------------------------------------------------------
    */

    public function citizenReports()
    {
        return $this->hasMany(
            CitizenReport::class
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Field Team Operation
    |--------------------------------------------------------------------------
    */

    public function fieldAssignments()
    {
        return $this->hasMany(
            FieldAssignment::class
        );
    }


    public function fieldVerifications()
    {
        return $this->hasMany(
            FieldVerification::class
        );
    }


    public function statusHistories()
    {
        return $this->hasMany(
            IncidentStatusHistory::class
        );
    }

}
