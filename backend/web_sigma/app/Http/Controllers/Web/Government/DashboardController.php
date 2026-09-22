<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\FireStatistic;
use App\Models\IncidentStatusHistory;
use App\Models\Hotspot;
use App\Models\Incident;
use App\Models\FireRisk;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik terbaru
        $statistics = FireStatistic::latest('statistic_date')->first();

        // Data dasar dashboard
        $data = config('sigma_data');

        // Statistik dari database
        if ($statistics) {
            $data['government_metrics'] = [
                [
                    'label' => 'Total Hotspot',
                    'value' => number_format($statistics->total_hotspots),
                    'icon' => '◉',
                    'accent' => 'danger',
                ],
                [
                    'label' => 'Insiden Aktif',
                    'value' => number_format($statistics->active_incidents),
                    'icon' => '🔥',
                    'accent' => 'orange',
                ],
                [
                    'label' => 'Insiden Selesai',
                    'value' => number_format($statistics->resolved_incident),
                    'icon' => '✓',
                    'accent' => 'success',
                ],
                [
                    'label' => 'Luas Terdampak',
                    'value' => number_format($statistics->affected_area) . ' Ha',
                    'icon' => '▣',
                    'accent' => 'warning',
                ],
                [
                    'label' => 'Wilayah Terdampak',
                    'value' => number_format($statistics->affected_region),
                    'icon' => '⌖',
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

        return view('government.dashboard', compact(
            'data',
            'statistics',
            'hotspots',
            'incidents',
            'fireRisks'
        ));
    }
}