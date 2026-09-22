<?php

namespace App\Models;

use App\Services\Citizen\CitizenNotificationService;
use Illuminate\Database\Eloquent\Model;

class ReportStatusHistory extends Model
{
    protected static function booted(): void
    {
        static::created(function (self $history): void {
            app(CitizenNotificationService::class)->reportStatusChanged($history);
        });
    }

    protected $fillable = [
        'citizen_report_id',
        'status',
        'description',
        'updated_by',
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
