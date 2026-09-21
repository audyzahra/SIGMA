<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceReport extends Model
{

    protected $fillable = [
        'user_id',
        'citizen_report_id',
        'audio_file',
        'transcript',
        'detected_location',
        'processing_status'
    ];



    protected $casts = [
        'detected_location'=>'array'
    ];



    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function citizenReport()
    {
        return $this->belongsTo(
            CitizenReport::class
        );
    }

}
