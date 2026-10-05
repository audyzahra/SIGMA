<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable([
    'name',
    'email',
    'password',
    'organization_id',
])]

#[Hidden([
    'password',
    'remember_token',
])]

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, LogsActivity;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(
            Organization::class
        );
    }

    public function fieldTeams()
    {
        return $this->belongsToMany(FieldTeam::class, 'field_team_user')
            ->withPivot(['online_status', 'last_seen_at'])
            ->withTimestamps();
    }

    public function auditLogs()
    {
        return $this->hasMany(
            AuditLog::class
        );
    }

    public function aiModels()
    {
        return $this->hasMany(
            AiModel::class,
            'created_by'
        );
    }

    public function citizenReports()
    {
        return $this->hasMany(
            CitizenReport::class
        );
    }

    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function governmentNotificationPreference(): HasOne
    {
        return $this->hasOne(
            GovernmentNotificationPreference::class
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'email',
                'organization_id',
            ])
            ->logOnlyDirty()
            ->useLogName('user');
    }
}
