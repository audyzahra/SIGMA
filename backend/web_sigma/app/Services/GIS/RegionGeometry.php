<?php

namespace App\Services\GIS;

use App\Models\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Utility geometry wilayah (MySQL Spatial).
 *
 * MySQL tidak mendukung ST_Centroid / ST_Envelope untuk geometry
 * SRID 4326 (error 3618), sehingga titik tengah wilayah dihitung dari
 * bounding box GeoJSON.
 *
 * Format GeoJSON: [longitude, latitude] -> latitude = Y, longitude = X.
 */
class RegionGeometry
{
    public const LEVELS = [
        'province',
        'regency',
        'district',
    ];

    /**
     * Ambil GeoJSON wilayah (bulk, satu query).
     *
     * @param  array<int, int>  $regionIds
     */
    public static function geojsonByRegionIds(array $regionIds): Collection
    {
        if (empty($regionIds)) {
            return collect();
        }

        return DB::table('regions')
            ->whereIn('id', $regionIds)
            ->select(['id'])
            ->selectRaw('ST_AsGeoJSON(geometry) AS geometry_json')
            ->get()
            ->keyBy('id');
    }

    /**
     * GeoJSON satu wilayah.
     */
    public static function geojsonForRegion(Region $region): ?string
    {
        $row = DB::table('regions')
            ->where('id', $region->id)
            ->select(['id'])
            ->selectRaw('ST_AsGeoJSON(geometry) AS geometry_json')
            ->first();

        return $row->geometry_json ?? null;
    }

    /**
     * Titik tengah wilayah (latitude, longitude) dari geometry.
     */
    public static function centerForRegion(Region $region): ?array
    {
        return self::centerFromGeoJson(
            self::geojsonForRegion($region)
        );
    }

    /**
     * Titik tengah dari GeoJSON (Polygon / MultiPolygon).
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public static function centerFromGeoJson(?string $geojson): ?array
    {
        if (! $geojson) {
            return null;
        }

        $geometry = json_decode($geojson, true);

        if (! is_array($geometry) || empty($geometry['coordinates'])) {
            return null;
        }

        $bounds = [
            'min_latitude' => null,
            'max_latitude' => null,
            'min_longitude' => null,
            'max_longitude' => null,
        ];

        self::collectCoordinates($geometry['coordinates'], $bounds);

        if ($bounds['min_latitude'] === null || $bounds['min_longitude'] === null) {
            return null;
        }

        return [
            'latitude' => ($bounds['min_latitude'] + $bounds['max_latitude']) / 2,
            'longitude' => ($bounds['min_longitude'] + $bounds['max_longitude']) / 2,
        ];
    }

    /**
     * Batas koordinat (rekursif untuk Polygon / MultiPolygon).
     */
    protected static function collectCoordinates(array $coordinates, array &$bounds): void
    {
        foreach ($coordinates as $coordinate) {

            if (! is_array($coordinate)) {
                continue;
            }

            if (
                isset($coordinate[0], $coordinate[1])
                && is_numeric($coordinate[0])
                && is_numeric($coordinate[1])
            ) {

                $longitude = (float) $coordinate[0];

                $latitude = (float) $coordinate[1];

                $bounds['min_latitude'] = $bounds['min_latitude'] === null
                    ? $latitude
                    : min($bounds['min_latitude'], $latitude);

                $bounds['max_latitude'] = $bounds['max_latitude'] === null
                    ? $latitude
                    : max($bounds['max_latitude'], $latitude);

                $bounds['min_longitude'] = $bounds['min_longitude'] === null
                    ? $longitude
                    : min($bounds['min_longitude'], $longitude);

                $bounds['max_longitude'] = $bounds['max_longitude'] === null
                    ? $longitude
                    : max($bounds['max_longitude'], $longitude);

                continue;

            }

            self::collectCoordinates($coordinate, $bounds);

        }
    }
}
