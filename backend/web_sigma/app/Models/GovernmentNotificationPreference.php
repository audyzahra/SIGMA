<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernmentNotificationPreference extends Model
{

    protected $fillable = [

        'user_id',

        'risk_alert',
        'critical_risk_alert',

        'citizen_report_alert',

        'field_team_alert',

        'ai_recommendation_alert',

    ];


    protected function casts(): array
    {
        return [

            'risk_alert' => 'boolean',

            'critical_risk_alert' => 'boolean',

            'citizen_report_alert' => 'boolean',

            'field_team_alert' => 'boolean',

            'ai_recommendation_alert' => 'boolean',

        ];
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

}
