<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\FireStatistic;
use App\Models\IncidentStatusHistory;
use App\Models\Hotspot;
use App\Models\Incident;
use App\Models\FireRisk;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik terbaru
        $statistics = FireStatistic::latest('statistic_date')->first();

        /*
         * Dashboard TIDAK memakai config('sigma_data') sebagai sumber angka.
         * Semua nilai di bawah berasal dari tabel nyata (fire_statistics,
         * incident_status_histories). Bila statistik belum pernah dihitung,
         * daftar metrik dibiarkan kosong supaya UI menampilkan keterangan
         * "belum tersedia" alih-alih angka contoh.
         */
        $data = [
            'government_metrics' => [],
            'recent_activities' => [],
        ];

        // Statistik dari database
        if ($statistics) {
            $data['government_metrics'] = [
                [
                    'label' => 'Total Hotspot',
                    'value' => number_format($statistics->total_hotspots),
                    'icon' => 'fire',
                    'accent' => 'danger',
                ],
                [
                    'label' => 'Insiden Aktif',
                    'value' => number_format($statistics->active_incidents),
                    'icon' => 'alert',
                    'accent' => 'orange',
                ],
                [
                    'label' => 'Insiden Selesai',
                    'value' => number_format($statistics->resolved_incident),
                    'icon' => 'check',
                    'accent' => 'success',
                ],
                [
                    'label' => 'Luas Terdampak',
                    'value' => number_format($statistics->affected_area) . ' Ha',
                    'icon' => 'map',
                    'accent' => 'warning',
                ],
                [
                    'label' => 'Wilayah Terdampak',
                    'value' => number_format($statistics->affected_region),
                    'icon' => 'location',
                    'accent' => 'info',
                ],
            ];
        }

        // Aktivitas terbaru
        $activities = IncidentStatusHistory::with('incident')
            ->latest()
            ->limit(5)
            ->get();

        $data['recent_activities'] = $activities->map(function ($activity) {
            return [
                'time' => $activity->created_at?->diffForHumans() ?? '-',

                'type' => match ($activity->status) {
                    'detected' => 'danger',
                    'on_process' => 'warning',
                    'extinguished' => 'success',
                    default => 'info',
                },

                'label' => match ($activity->status) {
                    'detected' => 'Insiden karhutla terdeteksi',
                    'on_process' => 'Penanganan insiden sedang berlangsung',
                    'extinguished' => 'Insiden berhasil dipadamkan',
                    default => 'Status insiden diperbarui',
                },
            ];
        })->toArray();

        // Hotspot aktif
        $hotspots = Hotspot::where('status', 'active')
            ->latest('detected_at')
            ->get();

        // Insiden aktif
        $incidents = Incident::whereIn('fire_status', [
                'detected',
                'on_process',
            ])
            ->latest('detected_at')
            ->get();

        // Risiko wilayah terbaru
        $fireRisks = FireRisk::with('region')
            ->latest('calculated_at')
            ->get();


            $regions = Region::query()
    ->whereIn('level', [
        'province',
        'regency',
        'district'
    ])
    ->select([
        'id',
        'parent_id',
        'name',
        'code',
        'level'
    ])
    ->orderBy('level')
    ->orderBy('name')
    ->get();


$geometryData = DB::table('regions')
    ->whereIn('level', [
        'province',
        'regency',
        'district'
    ])
    ->select([
        'id'
    ])
    ->selectRaw('ST_AsGeoJSON(geometry) AS geometry_json')
    ->get()
    ->keyBy('id');


$regions->each(function ($region) use ($geometryData) {

    $data = $geometryData->get($region->id);

    $region->setAttribute(
        'gis_geometry',
        $data?->geometry_json
    );

});


$riskByRegion = $fireRisks->keyBy('region_id');


$sigmaRegions = $regions->map(function ($region) use ($riskByRegion) {

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

})->values();

        return view('government.dashboard', compact(
            'data',
            'statistics',
            'hotspots',
            'incidents',
            'fireRisks',
            'sigmaRegions'
        ));
    }
}