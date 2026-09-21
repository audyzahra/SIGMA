<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportReward extends Model
{

    protected $fillable = [
        'user_id',
        'citizen_report_id',
        'reward_type',
        'amount',
        'status',
        'approved_by',
        'approved_at'
    ];



    protected $casts = [
        'approved_at' => 'datetime',
        'amount' => 'decimal:2'
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



    public function approver()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

}
