<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FireStatistic extends Model
{
    protected $fillable = [
        'total_hotspots',
        'active_incidents',
        'resolved_incident',
        'affected_area',
        'affected_region',
        'statistic_date',
    ];

    protected $casts = [
        'statistic_date' => 'date',
    ];
}
