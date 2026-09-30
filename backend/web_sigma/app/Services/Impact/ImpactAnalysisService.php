<?php

namespace App\Services\Impact;

use App\Models\CitizenReport;
use App\Models\FireRisk;
use App\Models\Hotspot;
use App\Models\ImpactAssessment;
use App\Models\Incident;
use App\Models\Region;
use App\Services\GIS\RegionGeometry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Analisis Dampak (Impact Analysis) berbasis spasial.
 *
 * Menjawab: "Jika terjadi karhutla di titik ini, wilayah/aset apa yang
 * berpotensi terdampak?"
 *
 * BUKAN analisis risiko (peluang kejadian) — itu tugas AI Fire Risk.
 *
 * Prinsip:
 *  - Semua angka berasal dari data nyata (database + perhitungan spatial).
 *  - Dataset yang belum tersedia DILAPORKAN tidak tersedia, bukan diisi 0
 *    atau angka karangan.
 *  - Tidak memakai LLM untuk menghitung kuantitas GIS.
 *  - Risk Score dan Impact Score adalah dua dimensi terpisah.
 */
class ImpactAnalysisService
{
    /** Level wilayah yang dipakai pada perhitungan. */
    public const LEVELS = RegionGeometry::LEVELS;

    /**
     * Komponen skor yang datanya BELUM tersedia di database SIGMA.
     * Dipakai untuk melaporkan keterbatasan secara jujur ke UI.
     */
    public const MISSING_COMPONENTS = [
        'population_exposure' => [
            'label' => 'Penduduk Terpapar',
            'source' => 'population',
        ],
        'settlement_exposure' => [
            'label' => 'Permukiman / Bangunan',
            'source' => 'settlement',
        ],
        'critical_facility_exposure' => [
            'label' => 'Fasilitas Kritis',
            'source' => 'critical_facility',
        ],
        'infrastructure_exposure' => [
            'label' => 'Infrastruktur Jalan',
            'source' => 'infrastructure',
        ],
        'environmental_exposure' => [
            'label' => 'Tutupan Lahan / Hutan',
            'source' => 'land_cover',
        ],
    ];

    public function __construct(
        protected RegionGeometry $geometry = new RegionGeometry()
    ) {
    }

    /* ==================================================================
     | KONFIGURASI
     | ================================================================== */

    public function methodologyVersion(): string
    {
        return (string) config('sigma_impact.methodology_version', 'impact-spatial-v1.0');
    }

    public function radiusOptions(): array
    {
        return config('sigma_impact.radius_options', [1, 3, 5, 10]);
    }

    public function defaultRadius(): float
    {
        return (float) config('sigma_impact.radius_default', 5);
    }

    /**
     * Validasi & pembatasan radius dari request.
     */
    public function normalizeRadius(mixed $radius): float
    {
        $min = (float) config('sigma_impact.radius_min_km', 0.5);
        $max = (float) config('sigma_impact.radius_max_km', 50);

        $value = is_numeric($radius) ? (float) $radius : $this->defaultRadius();

        return round(min(max($value, $min), $max), 2);
    }

    public function impactLevel(int $score): string
    {
        $levels = config('sigma_impact.levels', []);

        if ($score >= ($levels['critical'] ?? 80)) {
            return 'critical';
        }

        if ($score >= ($levels['high'] ?? 60)) {
            return 'high';
        }

        if ($score >= ($levels['medium'] ?? 40)) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Status ketersediaan sumber data (untuk transparansi UI).
     */
    public function dataSources(): array
    {
        return config('sigma_impact.data_sources', []);
    }
/* ==================================================================
     | ENTRY POINT
     | ================================================================== */

    /**
     * Analisis dampak dengan parameter fleksibel.
     *
     * Parameter yang didukung:
     *   region_id, incident_id, hotspot_id,
     *   latitude + longitude, radius_km
     */
    public function analyze(array $input): array
    {
        $radiusKm = $this->normalizeRadius($input['radius_km'] ?? null);

        $center = $this->resolveCenter($input);

        if (! $center) {
            return [
                'success' => false,
                'message' => 'Titik analisis belum ditentukan (pilih wilayah, hotspot, insiden, atau koordinat).',
                'data_sources' => $this->dataSources(),
            ];
        }

        return $this->cachedRun($center, $radiusKm, $input);
    }

    /**
     * Jalankan perhitungan spatial dengan cache hasil.
     *
     * ST_Intersects/ST_Intersection atas seluruh wilayah administratif
     * Indonesia memerlukan beberapa detik (tidak ada spatial index pada
     * regions.geometry), sedangkan halaman sering dibuka ulang untuk titik
     * yang sama (ganti radius, muat ulang, buka dari dashboard). Hasil
     * disimpan sementara sesuai config('sigma_impact.cache_seconds').
     *
     * Cache TIDAK mengubah nilai apa pun: hasil yang disimpan adalah
     * keluaran run() yang sama, hanya tidak dihitung ulang.
     */
    protected function cachedRun(array $center, float $radiusKm, array $input = []): array
    {
        $seconds = (int) config('sigma_impact.cache_seconds', 0);

        if ($seconds <= 0) {

            return $this->run($center, $radiusKm, $input);

        }

        $key = sprintf(
            'sigma_impact:%s:%s:%.5f:%.5f:%.2f',
            $this->methodologyVersion(),
            $center['source'],
            $center['latitude'],
            $center['longitude'],
            $radiusKm
        );

        return Cache::remember(
            $key,
            $seconds,
            fn () => $this->run($center, $radiusKm, $input)
        );
    }

    /**
     * Titik analisis dari wilayah (centroid geometry).
     */
    public function analyzeForRegion(Region $region, mixed $radiusKm = null, array $extra = []): array
    {
        $center = RegionGeometry::centerForRegion($region);

        if (! $center) {
            return [
                'success' => false,
                'message' => 'Wilayah ini belum memiliki geometri sehingga titik analisis tidak dapat dihitung.',
                'data_sources' => $this->dataSources(),
            ];
        }

        return $this->cachedRun(
            [
                'latitude' => (float) $center['latitude'],
                'longitude' => (float) $center['longitude'],
                'source' => 'region_centroid',
                'region' => $region,
            ],
            $this->normalizeRadius($radiusKm),
            $extra
        );
    }

    /**
     * Titik analisis dari insiden.
     */
    public function analyzeForIncident(Incident $incident, mixed $radiusKm = null): array
    {
        return $this->cachedRun(
            [
                'latitude' => (float) $incident->latitude,
                'longitude' => (float) $incident->longitude,
                'source' => 'incident',
                'incident' => $incident,
                'region' => $this->regionAt((float) $incident->latitude, (float) $incident->longitude),
            ],
            $this->normalizeRadius($radiusKm),
            ['incident_id' => $incident->id]
        );
    }
/* ==================================================================
     | RESOLVE TITIK ANALISIS
     | ================================================================== */

    protected function resolveCenter(array $input): ?array
    {
        if (! empty($input['incident_id'])) {

            $incident = Incident::find($input['incident_id']);

            if ($incident) {
                return [
                    'latitude' => (float) $incident->latitude,
                    'longitude' => (float) $incident->longitude,
                    'source' => 'incident',
                    'incident' => $incident,
                    'region' => $this->regionAt((float) $incident->latitude, (float) $incident->longitude),
                ];
            }

        }

        if (! empty($input['hotspot_id'])) {

            $hotspot = Hotspot::find($input['hotspot_id']);

            if ($hotspot) {
                return [
                    'latitude' => (float) $hotspot->latitude,
                    'longitude' => (float) $hotspot->longitude,
                    'source' => 'hotspot',
                    'hotspot' => $hotspot,
                    'region' => $this->regionAt((float) $hotspot->latitude, (float) $hotspot->longitude),
                ];
            }

        }

        if (! empty($input['region_id'])) {

            $region = Region::find($input['region_id']);

            if (! $region) {
                return null;
            }

            $center = RegionGeometry::centerForRegion($region);

            if (! $center) {
                return null;
            }

            return [
                'latitude' => (float) $center['latitude'],
                'longitude' => (float) $center['longitude'],
                'source' => 'region_centroid',
                'region' => $region,
            ];

        }

        if (isset($input['latitude'], $input['longitude'])
            && is_numeric($input['latitude'])
            && is_numeric($input['longitude'])) {

            $latitude = (float) $input['latitude'];
            $longitude = (float) $input['longitude'];

            return [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'source' => 'coordinate',
                'region' => $this->regionAt($latitude, $longitude),
            ];

        }

        return null;
    }

    /**
     * Wilayah terdalam (kecamatan > kabupaten > provinsi) yang
     * mengandung titik. Memakai urutan sumbu POINT(longitude, latitude)
     * sesuai kaidah SRID 4326 pada MySQL 8.
     */
    public function regionAt(float $latitude, float $longitude): ?Region
    {
        try {

            $row = DB::selectOne(
                '
                SELECT
                    (SELECT id FROM regions WHERE level = "district"
                        AND ST_Contains(geometry, ST_SRID(POINT(?, ?), 4326)) = 1 LIMIT 1) AS district_id,
                    (SELECT id FROM regions WHERE level = "regency"
                        AND ST_Contains(geometry, ST_SRID(POINT(?, ?), 4326)) = 1 LIMIT 1) AS regency_id,
                    (SELECT id FROM regions WHERE level = "province"
                        AND ST_Contains(geometry, ST_SRID(POINT(?, ?), 4326)) = 1 LIMIT 1) AS province_id
                ',
                [
                    $longitude, $latitude,
                    $longitude, $latitude,
                    $longitude, $latitude,
                ]
            );

            if (! $row) {
                return null;
            }

            $regionId = $row->district_id ?? $row->regency_id ?? $row->province_id;

            return $regionId ? Region::find($regionId) : null;

        } catch (\Throwable $e) {

            Log::warning('Gagal menentukan wilayah dari titik', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error' => $e->getMessage(),
            ]);

            return null;

        }
    }
/* ==================================================================
     | PERHITUNGAN SPASIAL
     | ================================================================== */

    /**
     * Jalankan analisis lengkap untuk satu titik + radius.
     */
    protected function run(array $center, float $radiusKm, array $input = []): array
    {
        $latitude = $center['latitude'];
        $longitude = $center['longitude'];

        $geojson = RegionGeometry::radiusPolygon($latitude, $longitude, $radiusKm);

        $circleAreaKm2 = M_PI * $radiusKm * $radiusKm;

        $affected = $this->affectedRegions($latitude, $longitude, $radiusKm, $geojson);

        $risk = $this->riskContext($affected, $circleAreaKm2);

        $hotspots = $this->hotspotContext($latitude, $longitude, $radiusKm);

        $incidents = $this->incidentContext($latitude, $longitude, $radiusKm);

        $reports = $this->reportContext($latitude, $longitude, $radiusKm);

        $score = $this->impactScore($affected, $risk, $hotspots, $incidents);

        $focalRisk = $center['region']
            ? $this->latestRiskForRegions([$center['region']->id])->get($center['region']->id)
            : null;

        return [
            'success' => true,
            'methodology_version' => $this->methodologyVersion(),
            'calculated_at' => now()->toDateTimeString(),

            'center' => [
                'latitude' => round($latitude, 6),
                'longitude' => round($longitude, 6),
                'source' => $center['source'],
                'radius_km' => $radiusKm,
                'zone_area_km2' => round($circleAreaKm2, 3),
            ],

            'region' => $center['region'] ? [
                'id' => $center['region']->id,
                'name' => $center['region']->name,
                'level' => $center['region']->level,
                'parent_id' => $center['region']->parent_id,
            ] : null,

            'risk_context' => [
                'region_name' => $center['region']->name ?? null,
                'risk_score' => $focalRisk?->risk_score,
                'risk_level' => $focalRisk?->risk_level,
                'ai_confidence' => $focalRisk?->ai_confidence,
                'calculated_at' => optional($focalRisk?->calculated_at)->toDateTimeString(),
                'source' => 'fire_risks (AI Service: NASA POWER + NASA FIRMS + model)',
            ],

            'affected_regions' => $affected,
            'risk' => $risk,
            'hotspots' => $hotspots,
            'incidents' => $incidents,
            'reports' => $reports,

            'metrics' => $this->metrics($affected, $risk, $hotspots, $incidents, $reports, $circleAreaKm2),

            'impact_score' => $score,

            'data_sources' => $this->dataSources(),
        ];
    }
/**
     * Wilayah administratif yang beririsan dengan zona analisis.
     *
     * Memakai ST_Intersects + ST_Intersection (MySQL 8, SRID 4326).
     * Placeholder GeoJSON dipakai dua kali, jadi binding dikirim dua kali.
     *
     * CATATAN PENTING (terbukti lewat probe MySQL 8.4):
     * ST_Intersection dapat mengembalikan GEOMETRYCOLLECTION berisi
     * POLYGON + LINESTRING + POINT (batas wilayah yang saling bersentuhan),
     * dan ST_Area() menolak tipe selain POLYGON/MULTIPOLYGON (error 3516).
     * Karena itu luas dihitung per bagian dengan ST_GeometryN lalu
     * hanya bagian polygon yang dijumlahkan.
     */
    protected function affectedRegions(
        float $latitude,
        float $longitude,
        float $radiusKm,
        string $geojson
    ): array {
        $circle = 'ST_SRID(ST_GeomFromGeoJSON(?), 4326)';

        try {

            /*
             * Klip wilayah dengan zona analisis memakai ST_Intersects +
             * ST_Intersection. Luas irisan TIDAK dihitung di SQL karena
             * hasil ST_Intersection bisa berupa GEOMETRYCOLLECTION
             * (POLYGON + LINESTRING + POINT) yang ditolak ST_Area
             * (error 3516). Luas dihitung di PHP dari GeoJSON hasil klip.
             */
            $rows = DB::select("
                SELECT
                    r.id,
                    r.parent_id,
                    r.name,
                    r.level,
                    r.region_km2,
                    ST_AsGeoJSON(r.intersect_geometry) AS intersect_geojson
                FROM (
                    SELECT
                        id,
                        parent_id,
                        name,
                        level,
                        ST_Area(geometry) / 1000000 AS region_km2,
                        ST_Intersection(geometry, {$circle}) AS intersect_geometry
                    FROM regions
                    WHERE level IN ('province', 'regency', 'district')
                      AND ST_Intersects(geometry, {$circle}) = 1
                ) AS r
                ORDER BY FIELD(r.level, 'district', 'regency', 'province')
                LIMIT 300
            ", [$geojson, $geojson]);

        } catch (\Throwable $e) {

            Log::error('Analisis spatial wilayah gagal', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius_km' => $radiusKm,
                'error' => $e->getMessage(),
            ]);

            return [
                'available' => false,
                'reason' => 'Perhitungan spatial gagal: ' . $e->getMessage(),
                'total' => 0,
                'by_level' => ['province' => [], 'regency' => [], 'district' => []],
                'counts' => ['province' => 0, 'regency' => 0, 'district' => 0],
                'area_km2' => 0.0,
                'area_hectares' => 0.0,
            ];

        }

        $byLevel = ['province' => [], 'regency' => [], 'district' => []];

        $totalIntersectKm2 = 0.0;

        foreach ($rows as $row) {

            $geometry = $row->intersect_geojson
                ? json_decode($row->intersect_geojson, true)
                : null;

            $intersectKm2 = self::geojsonAreaKm2($geometry);

            /* hanya wilayah yang benar-benar beririsan luas (bukan sentuh garis) */
            if ($intersectKm2 <= 0) {
                continue;
            }

            $byLevel[$row->level][] = [
                'id' => (int) $row->id,
                'parent_id' => $row->parent_id ? (int) $row->parent_id : null,
                'name' => $row->name,
                'level' => $row->level,
                'region_km2' => round((float) $row->region_km2, 3),
                'intersect_km2' => round($intersectKm2, 4),
                'intersect_hectares' => round($intersectKm2 * 100, 2),
                'intersect_percent_of_region' => (float) $row->region_km2 > 0
                    ? round($intersectKm2 / (float) $row->region_km2 * 100, 2)
                    : 0.0,
                /* geometri hasil klip dengan zona analisis (untuk layer peta) */
                'geometry' => $geometry,
            ];

        }

        /* urutkan tiap level dari irisan terbesar */
        foreach ($byLevel as $level => $entries) {

            usort($entries, fn ($a, $b) => $b['intersect_km2'] <=> $a['intersect_km2']);

            $byLevel[$level] = $entries;

        }

        /*
         * Luas terdampak dihitung dari SATU level terdalam saja
         * (kecamatan -> kabupaten -> provinsi) agar tidak terjadi
         * penghitungan ganda antar level administratif.
         */
        $primaryLevel = null;

        foreach (['district', 'regency', 'province'] as $level) {

            if (! empty($byLevel[$level])) {

                $primaryLevel = $level;

                foreach ($byLevel[$level] as $entry) {
                    $totalIntersectKm2 += $entry['intersect_km2'];
                }

                break;

            }

        }

        return [
            'available' => true,
            'reason' => null,
            'total' => count($byLevel['province']) + count($byLevel['regency']) + count($byLevel['district']),
            'primary_level' => $primaryLevel,
            'by_level' => $byLevel,
            'counts' => [
                'province' => count($byLevel['province']),
                'regency' => count($byLevel['regency']),
                'district' => count($byLevel['district']),
            ],
            'area_km2' => round($totalIntersectKm2, 3),
            'area_hectares' => round($totalIntersectKm2 * 100, 2),
        ];
    }
/**
     * Luas GeoJSON (km2) berbasis rumus spherical excess.
     *
     * Dipakai untuk menghitung luas hasil ST_Intersection di PHP, karena
     * MySQL menolak ST_Area() untuk GEOMETRYCOLLECTION (error 3516)
     * sedangkan hasil irisan bisa memuat POLYGON + LINESTRING + POINT.
     *
     * Hanya geometri bertipe Polygon/MultiPolygon yang dihitung; bagian
     * garis/titik (batas wilayah yang bersentuhan) diabaikan karena
     * luasnya nol.
     *
     * @param array|null $geojson Geometri GeoJSON hasil ST_AsGeoJSON
     */
    public static function geojsonAreaKm2(?array $geojson): float
    {
        if (! is_array($geojson) || empty($geojson['type'])) {
            return 0.0;
        }

        $type = $geojson['type'];

        if ($type === 'Polygon') {

            return self::polygonAreaKm2($geojson['coordinates'] ?? []);

        }

        if ($type === 'MultiPolygon') {

            $total = 0.0;

            foreach ($geojson['coordinates'] ?? [] as $polygon) {
                $total += self::polygonAreaKm2($polygon);
            }

            return $total;

        }

        if ($type === 'GeometryCollection') {

            $total = 0.0;

            foreach ($geojson['geometries'] ?? [] as $geometry) {
                $total += self::geojsonAreaKm2($geometry);
            }

            return $total;

        }

        /* LineString / Point tidak memiliki luas */
        return 0.0;
    }

    /**
     * Luas satu Polygon GeoJSON (km2) dengan lubang dikurangi.
     */
    protected static function polygonAreaKm2(array $rings): float
    {
        if (empty($rings)) {
            return 0.0;
        }

        $area = abs(self::ringAreaKm2($rings[0] ?? []));

        for ($index = 1; $index < count($rings); $index++) {
            $area -= abs(self::ringAreaKm2($rings[$index]));
        }

        return max($area, 0.0);
    }

    /**
     * Luas satu ring GeoJSON (km2), rumus spherical excess.
     *
     * @param array<int, array{0: float, 1: float}> $ring [longitude, latitude]
     */
    protected static function ringAreaKm2(array $ring): float
    {
        $count = count($ring);

        if ($count < 3) {
            return 0.0;
        }

        $earthRadius = RegionGeometry::EARTH_RADIUS_KM;

        $total = 0.0;

        for ($index = 0; $index < $count; $index++) {

            $current = $ring[$index];
            $next = $ring[($index + 1) % $count];

            if (! isset($current[0], $current[1], $next[0], $next[1])) {
                continue;
            }

            $total += deg2rad((float) $next[0] - (float) $current[0])
                * (2 + sin(deg2rad((float) $current[1])) + sin(deg2rad((float) $next[1])));

        }

        return abs($total * $earthRadius * $earthRadius / 2);
    }

    /* ==================================================================
     | KONTEKS WILAYAH TERDAMPAK
     | ================================================================== */

    /**
     * Risiko karhutla wilayah terdampak (dari tabel fire_risks).
     *
     * Mengambil satu risiko terbaru per wilayah, lalu menghitung
     * rata-rata risk_score dengan bobot luas irisan.
     */
    protected function riskContext(array $affected, float $circleAreaKm2): array
    {
        $districtIds = array_column($affected['by_level']['district'] ?? [], 'id');
        $regencyIds = array_column($affected['by_level']['regency'] ?? [], 'id');

        $regions = array_merge($districtIds, $regencyIds);

        if (empty($regions)) {
            return [
                'available' => true,
                'reason' => 'Tidak ada wilayah administratif yang beririsan dengan zona analisis.',
                'regions_with_risk' => 0,
                'weighted_risk_score' => null,
                'max_risk_score' => null,
                'risk_level_distribution' => [],
                'details' => [],
            ];
        }

        $risks = $this->latestRiskForRegions($regions);

        if ($risks->isEmpty()) {
            return [
                'available' => true,
                'reason' => 'Belum ada hasil analisis risiko AI untuk wilayah di zona ini.',
                'regions_with_risk' => 0,
                'weighted_risk_score' => null,
                'max_risk_score' => null,
                'risk_level_distribution' => [],
                'details' => [],
            ];
        }

        /* bobot luas irisan per wilayah */
        $weights = [];

        foreach (['district', 'regency'] as $level) {
            foreach ($affected['by_level'][$level] ?? [] as $row) {
                $weights[$row['id']] = $row['intersect_km2'];
            }
        }

        $weightedSum = 0.0;
        $weightTotal = 0.0;
        $maxScore = null;
        $distribution = [];
        $details = [];

        foreach ($risks as $regionId => $risk) {

            $score = (float) $risk->risk_score;

            $weight = $weights[$regionId] ?? 0.0;

            if ($weight <= 0) {
                continue;
            }

            $weightedSum += $score * $weight;
            $weightTotal += $weight;

            $maxScore = $maxScore === null ? $score : max($maxScore, $score);

            $level = (string) $risk->risk_level;

            $distribution[$level] = ($distribution[$level] ?? 0) + 1;

            $details[] = [
                'region_id' => (int) $regionId,
                'region_name' => $risk->region->name ?? null,
                'risk_score' => $score,
                'risk_level' => $risk->risk_level,
                'ai_confidence' => $risk->ai_confidence,
                'calculated_at' => optional($risk->calculated_at)->toDateTimeString(),
                'intersect_km2' => round($weight, 4),
            ];

        }

        return [
            'available' => true,
            'reason' => null,
            'regions_with_risk' => count($details),
            'weighted_risk_score' => $weightTotal > 0
                ? round($weightedSum / $weightTotal, 2)
                : null,
            'max_risk_score' => $maxScore !== null ? round($maxScore, 2) : null,
            'risk_level_distribution' => $distribution,
            'details' => $details,
        ];
    }

    /**
     * Risiko terbaru per wilayah (1 query, bukan N query).
     *
     * @return \Illuminate\Support\Collection<int, FireRisk>
     */
    protected function latestRiskForRegions(array $regionIds): \Illuminate\Support\Collection
    {
        if (empty($regionIds)) {
            return collect();
        }

        return FireRisk::with('region:id,name,level')
            ->whereIn('region_id', $regionIds)
            ->orderBy('calculated_at')
            ->orderBy('id')
            ->get()
            ->keyBy('region_id');
    }
/* ==================================================================
     | KONTEKS HOTSPOT / INSIDEN / LAPORAN (di dalam radius)
     | ================================================================== */

    /**
     * Hotspot di dalam radius analisis.
     *
     * Prefilter memakai bounding box (kolom latitude/longitude) lalu
     * jarak dihitung dengan haversine di PHP agar hasilnya deterministik
     * dan tidak bergantung pada fungsi geodesik MySQL.
     */
    protected function hotspotContext(float $latitude, float $longitude, float $radiusKm): array
    {
        $points = $this->pointsWithinRadius(
            Hotspot::query(),
            'latitude',
            'longitude',
            $latitude,
            $longitude,
            $radiusKm
        );

        $active = 0;
        $maxFrp = null;
        $maxBrightness = null;
        $nearest = null;
        $list = [];

        foreach ($points as $item) {

            /** @var Hotspot $hotspot */
            $hotspot = $item['model'];

            if ($hotspot->status === 'active') {
                $active++;
            }

            if ($hotspot->frp !== null) {
                $maxFrp = $maxFrp === null ? (float) $hotspot->frp : max($maxFrp, (float) $hotspot->frp);
            }

            if ($hotspot->brightness_temperature !== null) {
                $maxBrightness = $maxBrightness === null
                    ? (float) $hotspot->brightness_temperature
                    : max($maxBrightness, (float) $hotspot->brightness_temperature);
            }

            $nearest = $nearest === null ? $item['distance_km'] : min($nearest, $item['distance_km']);

            $list[] = [
                'id' => (int) $hotspot->id,
                'latitude' => (float) $hotspot->latitude,
                'longitude' => (float) $hotspot->longitude,
                'satellite_name' => $hotspot->satellite_name,
                'confidence_level' => $hotspot->confidence_level,
                'frp' => $hotspot->frp !== null ? (float) $hotspot->frp : null,
                'brightness_temperature' => $hotspot->brightness_temperature !== null
                    ? (float) $hotspot->brightness_temperature
                    : null,
                'status' => $hotspot->status,
                'detected_at' => optional($hotspot->detected_at)->toDateTimeString(),
                'distance_km' => $item['distance_km'],
            ];

        }

        usort($list, fn ($a, $b) => $a['distance_km'] <=> $b['distance_km']);

        return [
            'available' => true,
            'reason' => null,
            'count' => count($list),
            'active_count' => $active,
            'max_frp' => $maxFrp,
            'max_brightness' => $maxBrightness,
            'nearest_distance_km' => $nearest,
            'items' => $list,
        ];
    }
/**
     * Insiden di dalam radius analisis.
     */
    protected function incidentContext(float $latitude, float $longitude, float $radiusKm): array
    {
        $points = $this->pointsWithinRadius(
            Incident::query(),
            'latitude',
            'longitude',
            $latitude,
            $longitude,
            $radiusKm
        );

        $byStatus = [];
        $bySeverity = [];
        $nearest = null;
        $list = [];

        foreach ($points as $item) {

            /** @var Incident $incident */
            $incident = $item['model'];

            $byStatus[$incident->fire_status] = ($byStatus[$incident->fire_status] ?? 0) + 1;
            $bySeverity[$incident->severity_level] = ($bySeverity[$incident->severity_level] ?? 0) + 1;

            $nearest = $nearest === null ? $item['distance_km'] : min($nearest, $item['distance_km']);

            $list[] = [
                'id' => (int) $incident->id,
                'latitude' => (float) $incident->latitude,
                'longitude' => (float) $incident->longitude,
                'source_type' => $incident->source_type,
                'fire_status' => $incident->fire_status,
                'severity_level' => $incident->severity_level,
                'location_description' => $incident->location_description,
                'detected_at' => optional($incident->detected_at)->toDateTimeString(),
                'distance_km' => $item['distance_km'],
            ];

        }

        usort($list, fn ($a, $b) => $a['distance_km'] <=> $b['distance_km']);

        /* insiden yang belum selesai dianggap masih berjalan */
        $ongoing = ($byStatus['detected'] ?? 0) + ($byStatus['on_process'] ?? 0);

        return [
            'available' => true,
            'reason' => null,
            'count' => count($list),
            'ongoing_count' => $ongoing,
            'by_fire_status' => $byStatus,
            'by_severity' => $bySeverity,
            'nearest_distance_km' => $nearest,
            'items' => $list,
        ];
    }

    /**
     * Laporan masyarakat di dalam radius (informasi tambahan).
     */
    protected function reportContext(float $latitude, float $longitude, float $radiusKm): array
    {
        $points = $this->pointsWithinRadius(
            CitizenReport::query(),
            'latitude',
            'longitude',
            $latitude,
            $longitude,
            $radiusKm
        );

        $byStatus = [];
        $byType = [];
        $list = [];

        foreach ($points as $item) {

            /** @var CitizenReport $report */
            $report = $item['model'];

            $byStatus[$report->verification_status] = ($byStatus[$report->verification_status] ?? 0) + 1;
            $byType[$report->report_type] = ($byType[$report->report_type] ?? 0) + 1;

            $list[] = [
                'id' => (int) $report->id,
                'report_type' => $report->report_type,
                'verification_status' => $report->verification_status,
                'latitude' => (float) $report->latitude,
                'longitude' => (float) $report->longitude,
                'description' => $report->description,
                'distance_km' => $item['distance_km'],
            ];

        }

        usort($list, fn ($a, $b) => $a['distance_km'] <=> $b['distance_km']);

        return [
            'available' => true,
            'reason' => null,
            'count' => count($list),
            'by_verification_status' => $byStatus,
            'by_report_type' => $byType,
            'items' => $list,
        ];
    }

    /**
     * Ambil titik (hotspot/insiden/laporan) yang benar-benar berada
     * di dalam radius. Prefilter memakai bounding box, lalu haversine.
     *
     * @return array<int, array{model: mixed, distance_km: float}>
     */
    protected function pointsWithinRadius(
        \Illuminate\Database\Eloquent\Builder $query,
        string $latitudeColumn,
        string $longitudeColumn,
        float $latitude,
        float $longitude,
        float $radiusKm
    ): array {
        $latitudeDelta = $radiusKm / 111.32;

        $longitudeDelta = $radiusKm / max(111.32 * cos(deg2rad($latitude)), 0.01);

        $candidates = $query
            ->whereNotNull($latitudeColumn)
            ->whereNotNull($longitudeColumn)
            ->whereBetween($latitudeColumn, [$latitude - $latitudeDelta, $latitude + $latitudeDelta])
            ->whereBetween($longitudeColumn, [$longitude - $longitudeDelta, $longitude + $longitudeDelta])
            ->get();

        $result = [];

        foreach ($candidates as $model) {

            $distance = self::haversineKm(
                $latitude,
                $longitude,
                (float) $model->{$latitudeColumn},
                (float) $model->{$longitudeColumn}
            );

            if ($distance <= $radiusKm) {
                $result[] = [
                    'model' => $model,
                    'distance_km' => round($distance, 4),
                ];
            }

        }

        return $result;
    }

    /**
     * Jarak lingkaran besar (haversine) dalam kilometer.
     */
    public static function haversineKm(
        float $latitudeA,
        float $longitudeA,
        float $latitudeB,
        float $longitudeB
    ): float {
        $earthRadius = RegionGeometry::EARTH_RADIUS_KM;

        $deltaLat = deg2rad($latitudeB - $latitudeA);
        $deltaLng = deg2rad($longitudeB - $longitudeA);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($latitudeA)) * cos(deg2rad($latitudeB)) * sin($deltaLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
/* ==================================================================
     | IMPACT SCORE
     | ================================================================== */

    /**
     * Hitung Impact Score dari komponen yang datanya tersedia.
     *
     * Bobot diambil dari config/sigma_impact.php dan dinormalisasi ulang
     * bila ada komponen yang tidak tersedia, sehingga skor tidak pernah
     * "dibantu" oleh angka karangan.
     */
    protected function impactScore(array $affected, array $risk, array $hotspots, array $incidents): array
    {
        $weights = config('sigma_impact.weights', []);

        $normalization = config('sigma_impact.normalization', []);

        $components = [];

        /* ---------- 1. Intensitas hotspot (NASA FIRMS) ---------- */

        $frpReference = (float) ($normalization['hotspot_frp_reference'] ?? 50);
        $brightnessReference = (float) ($normalization['hotspot_brightness_reference'] ?? 360);
        $countReference = (float) ($normalization['hotspot_count_reference'] ?? 10);

        $frp = $hotspots['max_frp'];

        $frpPart = ($frp !== null && $frpReference > 0)
            ? min($frp / $frpReference, 1)
            : 0.0;

        $brightness = $hotspots['max_brightness'];

        /* baseline 300 K, di bawah nilai ini bukan anomali termal */
        $brightnessPart = $brightness !== null
            ? min(max(($brightness - 300) / max($brightnessReference - 300, 1), 0), 1)
            : 0.0;

        $countPart = $countReference > 0
            ? min($hotspots['count'] / $countReference, 1)
            : 0.0;

        $components['hotspot_intensity'] = [
            'label' => 'Intensitas Hotspot',
            'available' => true,
            'note' => $hotspots['count'] > 0
                ? 'Dihitung dari hotspot NASA FIRMS di dalam radius.'
                : 'Tidak ada hotspot terdeteksi di dalam radius (nilai 0, bukan data hilang).',
            'raw' => [
                'hotspot_count' => $hotspots['count'],
                'active_hotspot_count' => $hotspots['active_count'],
                'max_frp' => $frp,
                'max_brightness' => $brightness,
            ],
            'normalized' => round(
                (0.5 * $frpPart + 0.3 * $brightnessPart + 0.2 * $countPart) * 100,
                2
            ),
        ];

        /* ---------- 2. Konteks risiko wilayah (AI) ---------- */

        $components['fire_risk_context'] = [
            'label' => 'Risiko Wilayah Terdampak',
            'available' => $risk['weighted_risk_score'] !== null,
            'note' => $risk['weighted_risk_score'] !== null
                ? 'Rata-rata risk_score wilayah terdampak, dibobot luas irisan (hasil AI Service).'
                : ($risk['reason'] ?: 'Risiko wilayah tidak tersedia.'),
            'raw' => [
                'weighted_risk_score' => $risk['weighted_risk_score'],
                'max_risk_score' => $risk['max_risk_score'],
                'regions_with_risk' => $risk['regions_with_risk'],
            ],
            'normalized' => $risk['weighted_risk_score'],
        ];

        /* ---------- 3. Beban koordinasi administratif ---------- */

        $districtReference = (float) ($normalization['district_count_reference'] ?? 6);

        $districtCount = (int) ($affected['counts']['district'] ?? 0);

        $components['administrative_exposure'] = [
            'label' => 'Sebaran Wilayah Terdampak',
            'available' => (bool) $affected['available'],
            'note' => $districtCount > 0
                ? 'Jumlah kecamatan yang beririsan dengan zona analisis (beban koordinasi lintas wilayah).'
                : 'Tidak ada kecamatan yang beririsan dengan zona analisis.',
            'raw' => [
                'district_count' => $districtCount,
                'regency_count' => (int) ($affected['counts']['regency'] ?? 0),
                'province_count' => (int) ($affected['counts']['province'] ?? 0),
                'affected_area_km2' => $affected['area_km2'],
            ],
            'normalized' => $districtReference > 0
                ? round(min($districtCount / $districtReference, 1) * 100, 2)
                : 0.0,
        ];

        /* ---------- 4. Tekanan insiden ---------- */

        $incidentReference = (float) ($normalization['incident_count_reference'] ?? 3);

        $ongoing = (int) $incidents['ongoing_count'];

        $components['incident_pressure'] = [
            'label' => 'Tekanan Insiden',
            'available' => true,
            'note' => $ongoing > 0
                ? 'Insiden yang belum selesai di dalam radius analisis.'
                : 'Tidak ada insiden aktif di dalam radius analisis (nilai 0).',
            'raw' => [
                'incident_count' => $incidents['count'],
                'ongoing_count' => $ongoing,
                'by_fire_status' => $incidents['by_fire_status'],
            ],
            'normalized' => $incidentReference > 0
                ? round(min($ongoing / $incidentReference, 1) * 100, 2)
                : 0.0,
        ];

        return $this->combineComponents($components, $weights);
    }
/**
     * Gabungkan komponen menjadi skor akhir 0-100.
     */
    protected function combineComponents(array $components, array $weights): array
    {
        $usedWeight = 0.0;
        $totalWeight = 0.0;
        $sum = 0.0;

        $unavailable = [];

        foreach ($components as $key => $component) {

            $weight = (float) ($weights[$key] ?? 0.0);

            $totalWeight += $weight;

            if (! $component['available'] || $component['normalized'] === null) {

                $unavailable[] = [
                    'key' => $key,
                    'label' => $component['label'],
                    'reason' => $component['note'],
                    'weight' => $weight,
                ];

                continue;
            }

            $usedWeight += $weight;

            $sum += (float) $component['normalized'] * $weight;

        }

        $score = $usedWeight > 0
            ? (int) round($sum / $usedWeight)
            : 0;

        foreach ($components as $key => &$component) {

            $weight = (float) ($weights[$key] ?? 0.0);

            $component['weight'] = $weight;

            $component['weight_used'] = $component['available'] ? $weight : 0.0;

            $component['contribution'] = ($usedWeight > 0 && $component['available'])
                ? round((float) $component['normalized'] * $weight / $usedWeight, 2)
                : 0.0;

        }

        unset($component);

        /* Komponen yang datanya belum ada di SIGMA: dilaporkan, tidak dihitung */
        foreach (self::MISSING_COMPONENTS as $key => $meta) {

            $source = $this->dataSources()[$meta['source']] ?? null;

            $unavailable[] = [
                'key' => $key,
                'label' => $meta['label'],
                'reason' => $source['note'] ?? 'Dataset belum tersedia.',
                'weight' => 0.0,
            ];

        }

        return [
            'score' => $score,
            'level' => $this->impactLevel($score),
            'methodology_version' => $this->methodologyVersion(),
            'components' => $components,
            'unavailable_components' => $unavailable,
            'weights_used' => [
                'used' => round($usedWeight, 4),
                'total' => round($totalWeight, 4),
                'data_completeness_percent' => $totalWeight > 0
                    ? round($usedWeight / $totalWeight * 100, 2)
                    : 0.0,
            ],
            'notes' => config('sigma_impact.methodology_notes', []),
        ];
    }
/* ==================================================================
     | METRIK UNTUK UI
     | ================================================================== */

    /**
     * Daftar metrik dampak beserta status ketersediaannya.
     *
     * Metrik yang datasetnya belum ada tetap dikirim dengan
     * available=false + alasan, sehingga UI menampilkan
     * "Data tidak tersedia" alih-alih angka 0 yang menyesatkan.
     */
    /**
     * Katalog metrik dampak TANPA nilai (available=false).
     *
     * Dipakai UI sebelum ada analisis dijalankan, supaya label, satuan, dan
     * status ketersediaan yang tampil tetap sama dengan hasil analisis.
     *
     * Kunci, label, satuan, dan sumber WAJIB sinkron dengan metrics() di
     * bawah (urutan juga sama) agar UI dapat mengisi nilai berdasarkan key.
     */
    public function metricCatalog(): array
    {
        $sources = $this->dataSources();

        $pending = 'Belum ada analisis dijalankan untuk titik ini.';

        $entry = function (
            string $key,
            string $label,
            string $source,
            ?string $unit,
            ?string $reason
        ) use ($sources, $pending): array {

            return [
                'key' => $key,
                'label' => $label,
                'value' => null,
                'unit' => $unit,
                'available' => false,
                'reason' => $reason ?? ($sources[$source]['note'] ?? 'Dataset belum tersedia.'),
                'source' => $source,
            ];

        };

        return [
            $entry('affected_area', 'Luas Wilayah Terdampak', 'region_geometry', 'km²', $pending),
            $entry('affected_districts', 'Kecamatan Terdampak', 'region_geometry', 'kecamatan', $pending),
            $entry('affected_regencies', 'Kabupaten Terdampak', 'region_geometry', 'kabupaten', $pending),
            $entry('hotspot_count', 'Hotspot di Radius', 'hotspot', 'titik', $pending),
            $entry('incident_count', 'Insiden di Radius', 'incident', 'insiden', $pending),
            $entry('report_count', 'Laporan Masyarakat', 'citizen_report', 'laporan', $pending),
            $entry('weighted_risk_score', 'Risiko Wilayah Terdampak', 'fire_risk', 'skor 0-100', $pending),

            /* ------ dataset yang belum tersedia di SIGMA ------ */

            $entry('population_affected', 'Penduduk Terpapar', 'population', 'jiwa', null),
            $entry('affected_households', 'Rumah Tangga Terdampak', 'settlement', 'rumah', null),
            $entry('school_count', 'Sekolah Terdampak', 'critical_facility', 'sekolah', null),
            $entry('hospital_count', 'Fasilitas Kesehatan Terdampak', 'critical_facility', 'fasilitas', null),
            $entry('road_distance', 'Jarak ke Jalan Terdekat', 'infrastructure', 'km', null),
            $entry('forest_area', 'Luas Hutan Terdampak', 'land_cover', 'ha', null),
            $entry('peatland_area', 'Luas Lahan Gambut Terdampak', 'land_cover', 'ha', null),
        ];
    }

    public function metrics(
        array $affected,
        array $risk,
        array $hotspots,
        array $incidents,
        array $reports,
        float $circleAreaKm2
    ): array {
        $sources = $this->dataSources();

        $unavailable = static function (string $key, string $label, string $source, ?string $unit = null) use ($sources): array {
            return [
                'key' => $key,
                'label' => $label,
                'value' => null,
                'unit' => $unit,
                'available' => false,
                'reason' => $sources[$source]['note'] ?? 'Dataset belum tersedia.',
                'source' => $source,
            ];
        };

        $zoneCoverage = $circleAreaKm2 > 0
            ? round(min($affected['area_km2'] / $circleAreaKm2, 2) * 100, 2)
            : 0.0;

        return [
            [
                'key' => 'affected_area',
                'label' => 'Luas Wilayah Terdampak',
                'value' => $affected['area_km2'],
                'unit' => 'km²',
                'available' => (bool) $affected['available'],
                'reason' => $affected['reason'],
                'source' => 'region_geometry',
                'extra' => [
                    'hectares' => $affected['area_hectares'],
                    'zone_coverage_percent' => $zoneCoverage,
                ],
            ],
            [
                'key' => 'affected_districts',
                'label' => 'Kecamatan Terdampak',
                'value' => $affected['counts']['district'] ?? 0,
                'unit' => 'kecamatan',
                'available' => (bool) $affected['available'],
                'reason' => $affected['reason'],
                'source' => 'region_geometry',
            ],
            [
                'key' => 'affected_regencies',
                'label' => 'Kabupaten Terdampak',
                'value' => $affected['counts']['regency'] ?? 0,
                'unit' => 'kabupaten',
                'available' => (bool) $affected['available'],
                'reason' => $affected['reason'],
                'source' => 'region_geometry',
            ],
            [
                'key' => 'hotspot_count',
                'label' => 'Hotspot di Radius',
                'value' => $hotspots['count'],
                'unit' => 'titik',
                'available' => true,
                'reason' => null,
                'source' => 'hotspot',
                'extra' => [
                    'active' => $hotspots['active_count'],
                    'max_frp' => $hotspots['max_frp'],
                    'nearest_distance_km' => $hotspots['nearest_distance_km'],
                ],
            ],
            [
                'key' => 'incident_count',
                'label' => 'Insiden di Radius',
                'value' => $incidents['count'],
                'unit' => 'insiden',
                'available' => true,
                'reason' => null,
                'source' => 'incident',
                'extra' => [
                    'ongoing' => $incidents['ongoing_count'],
                    'by_severity' => $incidents['by_severity'],
                ],
            ],
            [
                'key' => 'report_count',
                'label' => 'Laporan Masyarakat',
                'value' => $reports['count'],
                'unit' => 'laporan',
                'available' => true,
                'reason' => null,
                'source' => 'citizen_report',
                'extra' => [
                    'by_verification_status' => $reports['by_verification_status'],
                ],
            ],
            [
                'key' => 'weighted_risk_score',
                'label' => 'Risiko Wilayah Terdampak',
                'value' => $risk['weighted_risk_score'],
                'unit' => 'skor 0-100',
                'available' => $risk['weighted_risk_score'] !== null,
                'reason' => $risk['reason'],
                'source' => 'fire_risk',
                'extra' => [
                    'max_risk_score' => $risk['max_risk_score'],
                    'regions_with_risk' => $risk['regions_with_risk'],
                    'risk_level_distribution' => $risk['risk_level_distribution'],
                ],
            ],

            /* ------ dataset yang belum tersedia di SIGMA ------ */

            $unavailable('population_affected', 'Penduduk Terpapar', 'population', 'jiwa'),
            $unavailable('affected_households', 'Rumah Tangga Terdampak', 'settlement', 'rumah'),
            $unavailable('school_count', 'Sekolah Terdampak', 'critical_facility', 'sekolah'),
            $unavailable('hospital_count', 'Fasilitas Kesehatan Terdampak', 'critical_facility', 'fasilitas'),
            $unavailable('road_distance', 'Jarak ke Jalan Terdekat', 'infrastructure', 'km'),
            $unavailable('forest_area', 'Luas Hutan Terdampak', 'land_cover', 'ha'),
            $unavailable('peatland_area', 'Luas Lahan Gambut Terdampak', 'land_cover', 'ha'),
        ];
    }
/* ==================================================================
     | PENYIMPANAN HASIL
     | ================================================================== */

    /**
     * Simpan hasil analisis ke tabel impact_assessments.
     *
     * Satu insiden menyimpan satu analisis terbaru (diperbarui bila
     * dihitung ulang). Untuk analisis titik (hotspot / koordinat) yang
     * tidak terikat insiden, satu kombinasi titik pusat + radius
     * menyimpan satu baris, sehingga klik "Simpan Hasil" berulang kali
     * atau seeder yang dijalankan ulang tidak menumpuk baris duplikat.
     */
    public function persist(
        array $analysis,
        ?int $incidentId = null,
        ?int $regionId = null
    ): ?ImpactAssessment {

        if (! ($analysis['success'] ?? false)) {
            return null;
        }

        $score = $analysis['impact_score']['score'] ?? 0;

        $attributes = [
            'incident_id' => $incidentId,
            'region_id' => $regionId ?? ($analysis['region']['id'] ?? null),
            'analysis_radius_km' => $analysis['center']['radius_km'],
        ];

        $keys = $incidentId
            ? ['incident_id' => $incidentId]
            : [
                'incident_id' => null,
                'region_id' => $regionId ?? ($analysis['region']['id'] ?? null),
                'analysis_radius_km' => $analysis['center']['radius_km'],
                'center_latitude' => $analysis['center']['latitude'],
                'center_longitude' => $analysis['center']['longitude'],
            ];

        $values = [
            'center_latitude' => $analysis['center']['latitude'],
            'center_longitude' => $analysis['center']['longitude'],
            'center_source' => $analysis['center']['source'],
            'affected_area' => $analysis['affected_regions']['area_km2'] ?? 0,
            'affected_province_count' => $analysis['affected_regions']['counts']['province'] ?? 0,
            'affected_regency_count' => $analysis['affected_regions']['counts']['regency'] ?? 0,
            'affected_district_count' => $analysis['affected_regions']['counts']['district'] ?? 0,
            'hotspot_count' => $analysis['hotspots']['count'] ?? 0,
            'incident_count' => $analysis['incidents']['count'] ?? 0,
            'report_count' => $analysis['reports']['count'] ?? 0,
            'risk_weighted_score' => $analysis['risk']['weighted_risk_score'] ?? null,
            'impact_score' => $score,
            'impact_level' => $analysis['impact_score']['level'] ?? null,
            'methodology_version' => $analysis['methodology_version'],
            'components' => $analysis['impact_score'],
            'exposure' => [
                'affected_regions' => $analysis['affected_regions'],
                'hotspots' => $analysis['hotspots'],
                'incidents' => $analysis['incidents'],
                'reports' => $analysis['reports'],
                'risk' => $analysis['risk'],
            ],
            'data_sources' => $analysis['data_sources'],
            'calculated_at' => now(),
        ];

        return ImpactAssessment::updateOrCreate(
            array_merge($keys, $attributes),
            $values
        );
    }
}