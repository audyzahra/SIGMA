<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'level',
        'geometry',
        'area_size',
    ];


    protected $casts = [
        'geometry' => 'array',
        'area_size' => 'decimal:2',
    ];


    public function parent()
    {
        return $this->belongsTo(
            Region::class,
            'parent_id'
        );
    }


    public function children()
    {
        return $this->hasMany(
            Region::class,
            'parent_id'
        );
    }


    public function organizations()
    {
        return $this->hasMany(
            Organization::class
        );
    }


    public function fireRisks()
    {
        return $this->hasMany(
            FireRisk::class
        );
    }


    public function fireRiskHistories()
    {
        return $this->hasMany(
            FireRiskHistory::class
        );
    }
}
