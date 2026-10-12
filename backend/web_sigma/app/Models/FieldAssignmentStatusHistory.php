<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldAssignmentStatusHistory extends Model
{
    protected $fillable = [
        'field_assignment_id',
        'status',
        'description',
        'updated_by',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(
            FieldAssignment::class,
            'field_assignment_id'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
