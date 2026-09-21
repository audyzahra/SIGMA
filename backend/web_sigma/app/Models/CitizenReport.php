<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitizenReport extends Model
{

    protected $fillable = [
        'user_id',
        'incident_id',
        'report_type',
        'latitude',
        'longitude',
        'photo',
        'video',
        'description',
        'verification_status'
    ];


    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];



    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function incident()
    {
        return $this->belongsTo(
            Incident::class
        );
    }


    public function aiVerification()
    {
        return $this->hasOne(
            AiReportVerification::class
        );
    }


    public function voiceReport()
    {
        return $this->hasOne(
            VoiceReport::class
        );
    }


    public function statusHistories()
    {
        return $this->hasMany(
            ReportStatusHistory::class
        );
    }


    public function reward()
    {
        return $this->hasOne(
            ReportReward::class
        );
    }

}
