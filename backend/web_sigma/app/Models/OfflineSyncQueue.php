<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineSyncQueue extends Model
{

    protected $table = 'offline_sync_queue';


    protected $fillable = [
        'data_type',
        'payload',
        'sync_status',
        'device_id'
    ];


    protected $casts = [
        'payload'=>'array'
    ];

}
