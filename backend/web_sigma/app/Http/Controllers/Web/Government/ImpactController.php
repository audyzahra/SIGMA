<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\Hotspot;
use App\Models\ImpactAssessment;
use App\Models\Incident;
use App\Models\Region;
use App\Services\Impact\ImpactAnalysisService;
use Illuminate\Http\Request;

/**
 * Analisis Dampak (Government).
 *
 * Menjawab: "Jika karhutla terjadi di titik/wilayah ini, wilayah apa yang
 * berpotensi terdampak?"
 *
 * Berbeda dari:
 *  - Analisis Risiko  (peluang kejadian, AI Service) -> fire_risks
 *  - Prioritas        (urutan penanganan)            -> incident_priorities
 *  - Rekomendasi      (tindakan yang disarankan)     -> recommendations
 *
 * Semua angka dampak dihitung ImpactAnalysisService dari data nyata dan
 * perhitungan spatial MySQL. Metrik yang datasetnya belum ada di SIGMA
 * ditandai "tidak tersedia" (bukan 0, bukan angka karangan).
 */
class ImpactController extends Controller
{
    public function __construct(
        protected ImpactAnalysisService $impact
    ) {
    }

    public function index(Request $request)
    {
        $provinces = Region::query()
            ->where('level', 'province')
            ->orderBy('name')
            ->get(['id', 'name']);

        $radiusOptions = $this->impact->radiusOptions();

        $radius = $this->impact->normalizeRadius(
            $request->query('radius', $this->impact->defaultRadius())
        );

        /*
         * Analisis dijalankan saat halaman dibuka bila pengguna sudah
         * memilih wilayah / hotspot / insiden (mis. dari dashboard).
         */
        $selected = $this->selectedContext($request);

        $analysis = null;

        if ($selected['region_id'] || $selected['hotspot_id'] || $selected['incident_id']) {

            $analysis = $this->impact->analyze([
                'region_id' => $selected['region_id'],
                'hotspot_id' => $selected['hotspot_id'],
                'incident_id' => $selected['incident_id'],
                'radius_km' => $radius,
            ]);

        }

        return view('government.impact', [
            'provinces' => $provinces,
            'radiusOptions' => $radiusOptions,
            'radius' => $radius,
            'selected' => $selected,
            'analysis' => $analysis,

            /* pilihan titik analisis selain wilayah */
            'hotspots' => Hotspot::query()
                ->orderByDesc('detected_at')
                ->limit(50)
                ->get(['id', 'latitude', 'longitude', 'satellite_name', 'status', 'detected_at']),

            'incidents' => Incident::query()
                ->orderByDesc('detected_at')
                ->limit(50)
                ->get(['id', 'latitude', 'longitude', 'location_description', 'fire_status', 'severity_level', 'detected_at']),

            /* analisis wilayah yang sudah tersimpan (hasil nyata) */
            'recentAnalyses' => ImpactAssessment::query()
                ->with(['region:id,name,level', 'incident:id,location_description,severity_level'])
                ->whereNotNull('methodology_version')
                ->highImpactFirst()
                ->limit(8)
                ->get(),

            'dataSources' => $this->impact->dataSources(),
            'metricCatalog' => $this->impact->metricCatalog(),
            'methodologyNotes' => config('sigma_impact.methodology_notes', []),
            'methodologyVersion' => $this->impact->methodologyVersion(),
        ]);
    }

    /**
     * Endpoint JSON: jalankan analisis dampak (GET).
     *
     * Parameter: region_id | hotspot_id | incident_id | latitude+longitude
     * dan radius_km. Tambahkan save=1 untuk menyimpan hasil analisis.
     */
    public function analysis(Request $request)
    {
        $result = $this->impact->analyze([
            'region_id' => $request->query('region_id'),
            'hotspot_id' => $request->query('hotspot_id'),
            'incident_id' => $request->query('incident_id'),
            'latitude' => $request->query('latitude'),
            'longitude' => $request->query('longitude'),
            'radius_km' => $request->query('radius_km', $this->impact->defaultRadius()),
        ]);

        return $this->respond($request, $result);
    }

    /**
     * Endpoint JSON: jalankan analisis dampak (POST).
     */
    public function analyze(Request $request)
    {
        $result = $this->impact->analyze([
            'region_id' => $request->input('region_id'),
            'hotspot_id' => $request->input('hotspot_id'),
            'incident_id' => $request->input('incident_id'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'radius_km' => $request->input('radius_km', $this->impact->defaultRadius()),
        ]);

        return $this->respond($request, $result);
    }

    /**
     * Simpan hasil bila diminta, lalu kirim respons JSON.
     */
    protected function respond(Request $request, array $result)
    {
        if ($result['success'] && $request->boolean('save')) {

            $saved = $this->impact->persist(
                $result,
                $request->filled('incident_id') ? (int) $request->input('incident_id') : null,
                $request->filled('region_id') ? (int) $request->input('region_id') : null
            );

            $result['saved'] = $saved ? [
                'id' => $saved->id,
                'impact_score' => $saved->impact_score,
                'impact_level' => $saved->impact_level,
                'calculated_at' => optional($saved->calculated_at)->toDateTimeString(),
            ] : null;

        }

        return response()->json($result);
    }

    /**
     * Titik analisis yang dipilih lewat query string.
     */
    protected function selectedContext(Request $request): array
    {
        return [
            'region_id' => $request->query('region_id'),
            'hotspot_id' => $request->query('hotspot_id'),
            'incident_id' => $request->query('incident_id'),
        ];
    }
}