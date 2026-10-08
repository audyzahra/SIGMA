<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\FieldTeam;
use App\Models\PriorityResult;
use App\Models\Region;
use App\Models\ResponseAction;
use App\Services\AI\AIRecommendationService;

class RecommendationController extends Controller
{
    public function index(
        AIRecommendationService $aiRecommendationService
    ) {
        /*
        |--------------------------------------------------------------------------
        | AMBIL HASIL PRIORITAS WILAYAH
        |--------------------------------------------------------------------------
        |
        | Sumber utama halaman Rekomendasi adalah PriorityResult.
        |
        | Alur:
        |
        | Dataset
        |   -> Risk / Impact Analysis
        |   -> PriorityCalculationService
        |   -> priority_results
        |   -> Ranking
        |
        | BUKAN lagi:
        |
        | IncidentPriority -> Incident -> location_description
        |
        */

        $priorities = PriorityResult::query()
            ->with('region')
            ->whereIn(
                'priority_level',
                ['critical', 'high']
            )
            ->orderByRaw("
                CASE priority_level
                    WHEN 'critical' THEN 1
                    WHEN 'high' THEN 2
                    ELSE 3
                END
            ")
            ->orderByDesc('priority_score')
            ->orderBy('ranking_position')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | HILANGKAN DUPLIKASI WILAYAH
        |--------------------------------------------------------------------------
        |
        | Satu wilayah hanya boleh muncul sekali di halaman rekomendasi.
        |
        */

        $priorities = $priorities
            ->filter(fn ($priority) => $priority->region)
            ->unique('region_id')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | PRIORITAS AKTIF
        |--------------------------------------------------------------------------
        |
        | Wilayah dengan ranking tertinggi menjadi wilayah yang dianalisis AI.
        |
        */

        $activePriority = $priorities->first();

        /*
        |--------------------------------------------------------------------------
        | QUEUE
        |--------------------------------------------------------------------------
        |
        | Prioritas berikutnya tetap tersedia sebagai antrean.
        |
        */

        $queue = $priorities
            ->slice(1)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | DEFAULT AI OUTPUT
        |--------------------------------------------------------------------------
        */

        $aiOutput = [
            'available' => false,
            'status' => 'no_priority',
            'provider' => null,
            'recommendation' => null,
            'priority_action' => null,
            'actions' => [],
            'reasoning' => null,
        ];

        /*
        |--------------------------------------------------------------------------
        | ANALISIS AI UNTUK WILAYAH AKTIF
        |--------------------------------------------------------------------------
        */

        if ($activePriority) {
            $region = $activePriority->region;

            /*
            |--------------------------------------------------------------------------
            | DATA HASIL PRIORITY ENGINE
            |--------------------------------------------------------------------------
            |
            | components dan sources berasal dari proses kalkulasi prioritas.
            | Kita kirim apa adanya supaya Gemini mengetahui dasar keputusan
            | tanpa membuat data baru / dummy.
            |
            */

            $components = is_array(
                $activePriority->components
            )
                ? $activePriority->components
                : [];

            $sources = is_array(
                $activePriority->sources
            )
                ? $activePriority->sources
                : [];

            /*
            |--------------------------------------------------------------------------
            | DETAIL UNTUK AI
            |--------------------------------------------------------------------------
            */

            $detail = [
                'region' => [
                    'id' => $region->id,

                    'name' => $region->name
                        ?? null,

                    'code' => $region->code
                        ?? null,

                    'level' => $region->level
                        ?? null,

                    'parent_name' => $region->parent_name
                        ?? null,
                ],

                'risk' => [
                    'score' =>
                        $activePriority->risk_score,

                    'level' =>
                        $activePriority->priority_level,

                    /*
                    |--------------------------------------------------------------------------
                    | Jangan membuat weather dummy.
                    |
                    | Jika data cuaca sudah disimpan di components/sources,
                    | ikut dikirim melalui dataset_context di bawah.
                    |--------------------------------------------------------------------------
                    */

                    'weather' => null,
                ],

                'impact' => [
                    'score' =>
                        $activePriority->impact_score,

                    'population' => null,

                    'land_cover' => null,
                ],

                'priority' => [
                    'score' =>
                        $activePriority->priority_score,

                    'level' =>
                        $activePriority->priority_level,

                    'ranking_position' =>
                        $activePriority->ranking_position,

                    'data_completeness' =>
                        $activePriority->data_completeness,

                    'methodology_version' =>
                        $activePriority->methodology_version,
                ],

                /*
                |--------------------------------------------------------------------------
                | KONTEKS DATA ASLI HASIL ANALISIS
                |--------------------------------------------------------------------------
                */

                'sources' => [
                    'priority_components' => $components,
                    'datasets' => $sources,
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | BUAT PAYLOAD AI
            |--------------------------------------------------------------------------
            */

            $aiPayload =
                $aiRecommendationService->payload(
                    $detail
                );

            /*
            |--------------------------------------------------------------------------
            | MINTA GEMINI MENGANALISIS
            |--------------------------------------------------------------------------
            */

            $aiOutput =
                $aiRecommendationService->recommend(
                    $aiPayload
                );
        }

        /*
        |--------------------------------------------------------------------------
        | DATA UNTUK KIRIM PETUGAS
        |--------------------------------------------------------------------------
        |
        | Bagian ini TIDAK diubah.
        |
        */

        $teams = FieldTeam::where(
            'status',
            'active'
        )->get();

        $regions = Region::all();

        $actions = ResponseAction::where(
            'is_active',
            true
        )->get();

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'government.recommendation',
            compact(
                'priorities',
                'activePriority',
                'queue',
                'aiOutput',
                'teams',
                'regions',
                'actions'
            )
        );
    }
}
