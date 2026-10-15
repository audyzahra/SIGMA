<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyWarningHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['early_warning_id', 'actor_id', 'action', 'snapshot', 'created_at', 'idempotency_key'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'created_at' => 'datetime'];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
