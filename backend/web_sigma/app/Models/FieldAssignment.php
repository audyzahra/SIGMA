<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Region;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\FieldAssignmentStatusHistory;

class FieldAssignment extends Model
{
    protected $fillable = [
        'incident_id',
        'region_id',
        'team_id',
        'assigned_by',
        'accepted_by',
        'priority_level',
        'status',
        'assigned_at',
        'accepted_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(
            Incident::class
        );
    }

    public function region()
    {
        return $this->belongsTo(
            Region::class,
            'region_id'
        );
    }

    public function team()
    {
        return $this->belongsTo(
            FieldTeam::class,
            'team_id'
        );
    }

    public function assigner()
    {
        return $this->belongsTo(
            User::class,
            'assigned_by'
        );
    }

    public function acceptor()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function navigationLogs()
    {
        return $this->hasMany(
            TeamNavigationLog::class,
            'assignment_id'
        );
    }

    public function statusHistories()
{
    return $this->hasMany(FieldAssignmentStatusHistory::class)
        ->orderBy('created_at');
}
}
