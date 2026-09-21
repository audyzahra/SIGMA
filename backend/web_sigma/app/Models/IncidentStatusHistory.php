<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentStatusHistory extends Model
{
    protected $table = 'incidents_status_histories';

    protected $fillable = [
        'incident_id',
        'updated_by',
        'status',
        'note',
        'evidence_photo',
    ];

    public function incident()
    {
        return $this->belongsTo(
            Incident::class
        );
    }

    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}
