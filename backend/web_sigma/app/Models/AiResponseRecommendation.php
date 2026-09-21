<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiResponseRecommendation extends Model
{

    protected $fillable = [
        'incident_id',
        'recommendation_type',
        'recommendation_text',
        'risk_summary',
        'confidence_score',
        'model_version',
        'decision_status',
        'approved_by',
        'generated_at'
    ];


    protected $casts = [
        'generated_at'=>'datetime'
    ];



    public function incident()
    {
        return $this->belongsTo(
            Incident::class
        );
    }


    public function approver()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

}
