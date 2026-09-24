<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;


class ResponseAction extends Model
{

    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'name',

        'is_active',

    ];



    /*
    |--------------------------------------------------------------------------
    | Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'is_active' => 'boolean',

    ];



    /*
    |--------------------------------------------------------------------------
    | Scope Action Aktif
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
