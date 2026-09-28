<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'area_size' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Parent Region
    |--------------------------------------------------------------------------
    */

    public function parent()
    {
        return $this->belongsTo(
            Region::class,
            'parent_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Child Regions
    |--------------------------------------------------------------------------
    */

    public function children()
    {
        return $this->hasMany(
            Region::class,
            'parent_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Organizations
    |--------------------------------------------------------------------------
    */

    public function organizations()
    {
        return $this->hasMany(
            Organization::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Fire Risks
    |--------------------------------------------------------------------------
    */

    public function fireRisks()
    {
        return $this->hasMany(
            FireRisk::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Fire Risk Histories
    |--------------------------------------------------------------------------
    */

    public function fireRiskHistories()
    {
        return $this->hasMany(
            FireRiskHistory::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Weather Records
    |--------------------------------------------------------------------------
    */

    public function weatherRecords()
    {
        return $this->hasMany(
            WeatherRecord::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Geometry GeoJSON
    |--------------------------------------------------------------------------
    |
    | Geometry disimpan sebagai MySQL Spatial.
    | Accessor ini mengubah geometry menjadi array GeoJSON
    | ketika $region->geometry dipanggil.
    |
    | PERHATIAN: accessor ini menjalankan 1 query + json_decode per model.
    | Jangan dipanggil di dalam perulangan wilayah dalam jumlah besar,
    | gunakan query bulk ST_AsGeoJSON (lihat FireRiskController@index).
    |
    */

    public function getGeometryAttribute()
    {
        if (!$this->id) {
            return null;
        }

        $result = DB::selectOne(
            '
            SELECT ST_AsGeoJSON(geometry) AS geojson
            FROM regions
            WHERE id = ?
            ',
            [$this->id]
        );

        if (!$result || !$result->geojson) {
            return null;
        }

        $geometry = json_decode(
            $result->geojson,
            true
        );

        return json_last_error() === JSON_ERROR_NONE
            ? $geometry
            : null;
    }
}
