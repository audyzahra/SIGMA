<?php

namespace App\Services\GIS;

use App\Models\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use function count;

/**
 * Utility geometry wilayah (MySQL Spatial).
 *
 * MySQL tidak mendukung ST_Centroid / ST_Envelope untuk geometry
 * SRID 4326 (error 3618), sehingga titik tengah wilayah dihitung dari
 * bounding box GeoJSON.
 *
 * Format GeoJSON: [longitude, latitude] -> latitude = Y, longitude = X.
 *
 * Catatan urutan sumbu (terbukti pada MySQL 8.4):
 *   ST_SRID(POINT(longitude, latitude), 4326)  -> benar
 *   ST_SRID(POINT(latitude, longitude), 4326)  -> error 3732
 */
class RegionGeometry
{
    public const LEVELS = [
        'province',
        'regency',
        'district',
    ];

    /** Radius rata-rata bumi (km) untuk perhitungan lingkaran geodesik. */
    public const EARTH_RADIUS_KM = 6371.0088;

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
     * Lingkaran geodesik (zona analisis radius) sebagai GeoJSON Polygon.
     *
     * GeoJSON memakai urutan [longitude, latitude] sehingga hasilnya
     * langsung kompatibel dengan ST_GeomFromGeoJSON.
     *
     * @return string GeoJSON Polygon
     */
    public static function radiusPolygon(
        float $latitude,
        float $longitude,
        float $radiusKm,
        int $steps = 96
    ): string {
        $steps = max($steps, 16);

        $angularDistance = $radiusKm / self::EARTH_RADIUS_KM;

        $latRad = deg2rad($latitude);
        $lngRad = deg2rad($longitude);

        $points = [];

        for ($i = 0; $i <= $steps; $i++) {

            $bearing = 2 * M_PI * $i / $steps;

            $pointLat = asin(
                sin($latRad) * cos($angularDistance)
                + cos($latRad) * sin($angularDistance) * cos($bearing)
            );

            $pointLng = $lngRad + atan2(
                sin($bearing) * sin($angularDistance) * cos($latRad),
                cos($angularDistance) - sin($latRad) * sin($pointLat)
            );

            $points[] = [
                round(rad2deg($pointLng), 6),
                round(rad2deg($pointLat), 6),
            ];

        }

        return json_encode([
            'type' => 'Polygon',
            'coordinates' => [$points],
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Ekspresi SQL untuk zona analisis radius (SRID 4326).
     *
     * Mengembalikan string SQL yang HARUS diikuti binding GeoJSON
     * sebanyak jumlah placeholder ($placeholders).
     */
    public static function radiusSql(int $placeholders = 1): string
    {
        return str_repeat('ST_SRID(ST_GeomFromGeoJSON(?), 4326)', max($placeholders, 1));
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
