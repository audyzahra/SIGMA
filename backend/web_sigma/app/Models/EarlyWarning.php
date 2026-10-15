<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyWarning extends Model
{
    protected $fillable = [
        'incident_id', 'region_id', 'issued_by', 'level', 'status', 'source',
        'area', 'reason', 'instructions', 'issued_at', 'expires_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime', 'expires_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function isCurrentlyActive(): bool
    {
        return $this->status === 'active' && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function histories()
    {
        return $this->hasMany(EarlyWarningHistory::class);
    }
}
