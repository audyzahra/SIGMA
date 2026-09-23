<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldTeam extends Model
{
    protected $fillable = [
        'organization_id',
        'team_name',
        'leader_name',
        'phone',
        'status',
    ];

    public function organization()
    {
        return $this->belongsTo(
            Organization::class
        );
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'field_team_user')
            ->withPivot(['online_status', 'last_seen_at'])
            ->withTimestamps();
    }

    public function assignments()
    {
        return $this->hasMany(
            FieldAssignment::class,
            'team_id'
        );
    }

    public function responseAssignments()
    {
        return $this->hasMany(
            ResponseAssignment::class,
            'team_id'
        );
    }

    public function verifications()
    {
        return $this->hasMany(
            FieldVerification::class,
            'team_id'
        );
    }
}
