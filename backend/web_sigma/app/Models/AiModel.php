<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{

    protected $fillable = [
        'name',
        'code',
        'version',
        'description',
        'type',
        'input_type',
        'output_type',
        'framework',
        'algorithm',
        'model_file',
        'model_size',
        'accuracy',
        'precision_score',
        'recall_score',
        'f1_score',
        'training_dataset',
        'trained_at',
        'deployed_at',
        'endpoint_url',
        'status',
        'created_by'
    ];


    protected $casts = [
        'trained_at'=>'datetime',
        'deployed_at'=>'datetime',
    ];



    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public function verifications()
    {
        return $this->hasMany(
            AiReportVerification::class,
            'model_id'
        );
    }

}
