<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aspiration extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'organization',
        'region',
        'description',
        'status',
    ];
}