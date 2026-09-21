<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportStatusHistory extends Model
{

    protected $fillable = [
        'citizen_report_id',
        'status',
        'description',
        'updated_by'
    ];



    public function citizenReport()
    {
        return $this->belongsTo(
            CitizenReport::class
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
