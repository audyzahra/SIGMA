<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemProfile extends Model
{

    protected $fillable = [
        'title',
        'tagline',
        'hero_image',
        'total_hotspot_label',
        'total_incident_label',
        'contact_email',
        'contact_phone'
    ];

}
