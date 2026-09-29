<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\FireRisk;
use App\Services\AI\AIService;
use App\Services\AI\FireRiskPredictionService;
use App\Services\GIS\RegionGeometry;
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
     *    â””â”€â”€ Regency
     *          |
     *          â””â”€â”€ District
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
     * Perbarui analisis risiko memakai AI Service (FastAPI).
     *
     * Alur:
     *   regions (geometry) -> titik tengah GeoJSON (RegionGeometry)
     *   -> AI POST /predict-region (NASA POWER + NASA FIRMS + model)
     *   -> normalisasi + simpan lewat FireRiskPredictionService
     *   -> respon JSON untuk frontend.
     *
     * Ketika AI Service offline, respon tetap JSON (success = false)
     * sehingga halaman tidak pernah HTTP 500.
     */
    public function aiRefresh(
        Request $request,
        AIService $ai,
        FireRiskPredictionService $predictionService
    ) {
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

        $geometries = RegionGeometry::geojsonByRegionIds(
            $regions->pluck('id')->all()
        );

        $updated = [];

        $failed = [];

        foreach ($regions as $region) {

            $center = RegionGeometry::centerFromGeoJson(
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

            /*
            |--------------------------------------------------------------------------
            | Normalisasi + simpan
            |--------------------------------------------------------------------------
            |
            | FireRiskPredictionService menangani normalisasi risk_level
            | (LOW / MEDIUM / HIGH) serta penulisan fire_risks dan riwayatnya,
            | termasuk metadata sumber data AI (NASA POWER / NASA FIRMS).
            |
            */

            $row = $predictionService->store($region, $prediction, $center);

            if (! $row) {

                $failed[] = [
                    'region_id' => $region->id,
                    'name' => $region->name,
                    'message' => $prediction['message']
                        ?? 'Hasil AI tidak dikenali atau AI Service tidak dapat dihubungi.',
                ];

                continue;

            }

            $updated[] = $row;

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
}
