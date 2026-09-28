<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\FireRisk;
use App\Models\FireRiskHistory;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FireRiskController extends Controller
{
    /**
     * Halaman Risiko Karhutla
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil seluruh wilayah
        |--------------------------------------------------------------------------
        */

        $regions = Region::query()
            ->whereIn('level', [
                'province',
                'regency',
                'district',
            ])
            ->select([
                'id',
                'parent_id',
                'name',
                'code',
                'level',
            ])
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ambil geometry secara langsung dari MySQL
        |--------------------------------------------------------------------------
        */

        $geometryData = DB::table('regions')
            ->whereIn('level', [
                'province',
                'regency',
                'district',
            ])
            ->select([
                'id',
            ])
            ->selectRaw('ST_AsGeoJSON(geometry) AS geometry_json')
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | Gabungkan geometry ke masing-masing region
        |--------------------------------------------------------------------------
        */

        $regions->each(function ($region) use ($geometryData) {
            $data = $geometryData->get($region->id);

            /*
            |--------------------------------------------------------------------------
            | GeoJSON disimpan sebagai string, belum di-decode
            |--------------------------------------------------------------------------
            |
            | Decode 7231 geometry sekaligus membuat pemakaian memory melonjak
            | dan request berhenti dengan HTTP 500 (memory exhausted).
            |
            | String ini diteruskan apa adanya ke JavaScript, kemudian
            | di-JSON.parse di sisi browser (lihat gis-map.js).
            |
            */

            $region->setAttribute(
                'gis_geometry',
                $data && $data->geometry_json
                    ? $data->geometry_json
                    : null
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Dropdown Provinsi
        |--------------------------------------------------------------------------
        */

        $provinces = Region::query()
            ->where('level', 'province')
            ->select([
                'id',
                'parent_id',
                'name',
                'code',
                'level',
            ])
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Data Risiko
        |--------------------------------------------------------------------------
        */

        $fireRisks = FireRisk::query()
            ->orderByDesc('calculated_at')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Index Fire Risk berdasarkan region_id
        |--------------------------------------------------------------------------
        */

        $riskByRegion = $fireRisks->keyBy('region_id');

        /*
        |--------------------------------------------------------------------------
        | Data untuk JavaScript / GIS Map
        |--------------------------------------------------------------------------
        */

        $sigmaRegions = $regions
            ->map(function ($region) use ($riskByRegion) {

                $risk = $riskByRegion->get($region->id);

                return [
                    'id' => $region->id,

                    'name' => $region->name,

                    'code' => $region->code,

                    'level' => $region->level,

                    'parent_id' => $region->parent_id,

                    'geometry' => $region->gis_geometry,

                    'risk_score' => $risk?->risk_score ?? 0,

                    'risk_level' => $risk?->risk_level ?? 'LOW',

                    'temperature' => $risk?->temperature,

                    'humidity' => $risk?->humidity,

                    'wind_speed' => $risk?->wind_speed,

                    'rainfall' => $risk?->rainfall,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Kirim ke Blade
        |--------------------------------------------------------------------------
        */

        return view('government.fire-risk', [
            'regions' => $regions,
            'provinces' => $provinces,
            'fireRisks' => $fireRisks,
            'sigmaRegions' => $sigmaRegions,
        ]);
    }

    /**
     * Ambil child wilayah.
     *
     * Province
     *    |
     *    └── Regency
     *          |
     *          └── District
     */
    public function children($id)
    {
        $children = Region::query()
            ->where('parent_id', $id)
            ->select([
                'id',
                'parent_id',
                'name',
                'code',
                'level',
            ])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $children,
        ]);
    }

    /**
     * Skor risiko per level.
     *
     * Mengikuti konvensi data fire_risks yang sudah ada
     * (LOW = 30, MEDIUM = 60, HIGH = 72).
     */
    protected const RISK_SCORES = [
        'LOW' => 30,
        'MEDIUM' => 60,
        'HIGH' => 72,
    ];

    /**
     * Perbarui analisis risiko memakai AI Service (FastAPI).
     *
     * Alur:
     *   regions (geometry) -> titik tengah dari GeoJSON
     *   -> AI POST /predict-region
     *   -> simpan ke fire_risks + fire_risk_histories
     *   -> respon JSON untuk frontend.
     *
     * Ketika AI Service offline, respon tetap JSON (success = false)
     * sehingga halaman tidak pernah HTTP 500.
     */
    public function aiRefresh(Request $request, AIService $ai)
    {
        $validated = $request->validate([
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
        ]);

        $regionId = $validated['region_id'] ?? null;

        $regions = Region::query()
            ->whereIn('level', ['province', 'regency', 'district'])
            ->when(
                $regionId,
                fn ($query) => $query->whereKey($regionId)
            )
            ->when(
                ! $regionId,
                fn ($query) => $query->where('level', 'province')
            )
            ->select(['id', 'name', 'level'])
            ->orderBy('id')
            ->get();

        if ($regions->isEmpty()) {

            return response()->json([
                'success' => false,
                'ai_url' => $ai->url(),
                'updated' => [],
                'failed' => [],
                'message' => 'Tidak ada wilayah yang dapat dianalisis.',
            ]);

        }

        $geometries = DB::table('regions')
            ->whereIn('id', $regions->pluck('id'))
            ->select(['id'])
            ->selectRaw('ST_AsGeoJSON(geometry) AS geometry_json')
            ->get()
            ->keyBy('id');

        $updated = [];

        $failed = [];

        foreach ($regions as $region) {

            $center = $this->geometryCenter(
                optional($geometries->get($region->id))->geometry_json
            );

            if (! $center) {

                $failed[] = [
                    'region_id' => $region->id,
                    'name' => $region->name,
                    'message' => 'Geometry wilayah belum tersedia.',
                ];

                continue;

            }

            $prediction = $ai->predictRegion(
                $center['latitude'],
                $center['longitude']
            );

            $level = strtoupper((string) ($prediction['risk_level'] ?? ''));

            if (! ($prediction['success'] ?? false) || ! isset(self::RISK_SCORES[$level])) {

                $failed[] = [
                    'region_id' => $region->id,
                    'name' => $region->name,
                    'message' => $prediction['message']
                        ?? ('Hasil AI tidak dikenali: ' . $level),
                ];

                continue;

            }

            $attributes = [
                'risk_score' => self::RISK_SCORES[$level],
                'risk_level' => $level,
                'calculated_at' => now(),
            ];

            if (isset($prediction['confidence']) && is_numeric($prediction['confidence'])) {

                $attributes['ai_confidence'] = round(
                    (float) $prediction['confidence'],
                    2
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Parameter cuaca
            |--------------------------------------------------------------------------
            |
            | AI dapat mengembalikan nilai 0 ketika baris dataset terdekat tidak
            | memiliki data cuaca. Nilai 0 tidak ditulis agar halaman tidak
            | menampilkan suhu/kelembapan yang menyesatkan.
            |
            */

            foreach (['temperature', 'humidity', 'rainfall', 'wind_speed'] as $parameter) {

                $value = $prediction[$parameter] ?? null;

                if ($value !== null && is_numeric($value) && (float) $value > 0) {

                    $attributes[$parameter] = (float) $value;

                }

            }

            $fireRisk = FireRisk::updateOrCreate(
                ['region_id' => $region->id],
                $attributes
            );

            FireRiskHistory::create([
                'region_id' => $region->id,
                'risk_score' => $attributes['risk_score'],
                'risk_level' => $attributes['risk_level'],
                'calculated_at' => $attributes['calculated_at'],
            ]);

            $updated[] = [
                'region_id' => $region->id,
                'name' => $region->name,
                'level' => $region->level,
                'risk_score' => $fireRisk->risk_score,
                'risk_level' => $fireRisk->risk_level,
                'temperature' => $fireRisk->temperature,
                'humidity' => $fireRisk->humidity,
                'wind_speed' => $fireRisk->wind_speed,
                'rainfall' => $fireRisk->rainfall,
                'confidence' => $attributes['ai_confidence'] ?? null,
                'calculated_at' => optional($fireRisk->calculated_at)
                    ->format('Y-m-d H:i:s'),
                'latitude' => round($center['latitude'], 4),
                'longitude' => round($center['longitude'], 4),
            ];

        }

        $success = count($updated) > 0;

        return response()->json([
            'success' => $success,
            'ai_url' => $ai->url(),
            'updated' => $updated,
            'failed' => $failed,
            'message' => $success
                ? 'Analisis AI diperbarui untuk ' . count($updated) . ' wilayah.'
                : 'AI Service tidak dapat dihubungi atau tidak ada wilayah yang dapat dianalisis.',
        ]);
    }

    /**
     * Titik tengah wilayah dari GeoJSON.
     *
     * MySQL tidak mendukung ST_Centroid / ST_Envelope untuk geometry
     * SRID 4326 (error 3618), sehingga titik tengah dihitung dari
     * bounding box koordinat GeoJSON.
     */
    protected function geometryCenter(?string $geojson): ?array
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

        $this->collectCoordinates($geometry['coordinates'], $bounds);

        if ($bounds['min_latitude'] === null || $bounds['min_longitude'] === null) {
            return null;
        }

        return [
            'latitude' => ($bounds['min_latitude'] + $bounds['max_latitude']) / 2,
            'longitude' => ($bounds['min_longitude'] + $bounds['max_longitude']) / 2,
        ];
    }

    /**
     * Kumpulkan batas koordinat (rekursif: Polygon / MultiPolygon).
     */
    protected function collectCoordinates(array $coordinates, array &$bounds): void
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

            $this->collectCoordinates($coordinate, $bounds);

        }
    }
}
