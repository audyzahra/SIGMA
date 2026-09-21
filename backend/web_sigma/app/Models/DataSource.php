<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{

    protected $fillable = [
        'name',
        'provider',
        'type',
        'api_endpoint',
        'credentials_key',
        'status',
        'last_sync_at'
    ];


    protected $casts = [
        'last_sync_at'=>'datetime'
    ];


    public function hotspots()
    {
        return $this->hasMany(
            Hotspot::class,
            'source_id'
        );
    }

}
