<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiReportVerification extends Model
{

    protected $fillable = [
        'citizen_report_id',
        'model_id',
        'prediction_result',
        'confidence_score',
        'detected_objects',
        'verification_status',
        'verified_at'
    ];


    protected $casts = [
        'detected_objects'=>'array',
        'verified_at'=>'datetime',
    ];



    public function citizenReport()
    {
        return $this->belongsTo(
            CitizenReport::class
        );
    }


    public function model()
    {
        return $this->belongsTo(
            AiModel::class,
            'model_id'
        );
    }

}
