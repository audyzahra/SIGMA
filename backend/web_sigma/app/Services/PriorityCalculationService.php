<?php

namespace App\Services;

use App\Models\FireRisk;
use App\Models\ImpactAssessment;
use App\Models\PriorityResult;
use App\Models\Region;
use App\Services\GIS\RegionGeometry;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Priority Calculation Engine — Prioritas Penanganan.
 *
 * Tanggung jawab:
 *  1. mengambil data risiko kebakaran   (fire_risks, hasil AI Service)
 *  2. mengambil data dampak wilayah     (impact_assessments, analisis GIS)
 *  3. menghitung Priority Score         (bobot dari config/sigma_priority.php)
 *  4. menentukan kategori prioritas     (critical / high / medium / low)
 *  5. menyimpan + mengurutkan hasil     (priority_results, ranking_position)
 *
 * Prinsip yang dipegang modul ini:
 *  - Tidak ada angka hasil analisis yang ditulis manual (hardcode).
 *  - Tidak ada perhitungan di sisi klien; seluruh angka berasal dari service
 *    ini dan disimpan di tabel priority_results.
 *  - Nilai yang belum tersedia DILAPORKAN belum tersedia (null), bukan diisi
 *    nol, karena nol berarti "tidak berisiko / tidak terdampak".
 *  - Bobot komponen yang datanya belum tersedia dikeluarkan lalu bobot
 *    komponen tersisa dinormalisasi ulang, dan persentase kelengkapan data
 *    dicatat supaya keputusan tetap dapat diaudit.
 *
 * Kelas ini tidak memakai AI/LLM untuk menghitung apa pun. Keluaran service
 * hanya data; penyusunan rekomendasi bahasa ada di AIRecommendationService.
 */
class PriorityCalculationService
{
    /**
     * Nilai bawaan konfigurasi.
     *
     * Config aplikasi (config/sigma_priority.php) menimpa nilai ini secara
     * rekursif. Nilai bawaan diperlukan agar service tetap dapat dipakai dan
     * diuji tanpa memuat aplikasi Laravel.
     */
    public const DEFAULT_CONFIG = [
        'methodology_version' => 'priority-hybrid-v1.0',

        'weights' => [
            'risk' => 0.6,
            'impact' => 0.4,
        ],

        'components' => [
            'risk' => [
                'label' => 'Risiko',
                'label_long' => 'Skor Risiko Karhutla',
                'source' => 'Fire Risk Model',
                'source_detail' => '',
            ],
            'impact' => [
                'label' => 'Dampak',
                'label_long' => 'Skor Dampak Wilayah',
                'source' => 'Population Dataset + GIS Analysis',
                'source_detail' => '',
            ],
        ],

        'level_order' => ['critical', 'high', 'medium', 'low'],

        'levels' => [
            'critical' => [
                'threshold' => 80,
                'label' => 'Kritis',
                'badge' => 'priority-kritis',
                'accent' => 'danger',
                'note' => '',
            ],
            'high' => [
                'threshold' => 60,
                'label' => 'Tinggi',
                'badge' => 'priority-tinggi',
                'accent' => 'orange',
                'note' => '',
            ],
            'medium' => [
                'threshold' => 40,
                'label' => 'Sedang',
                'badge' => 'priority-sedang',
                'accent' => 'warning',
                'note' => '',
            ],
            'low' => [
                'threshold' => 0,
                'label' => 'Rendah',
                'badge' => 'priority-rendah',
                'accent' => 'green',
                'note' => '',
            ],
        ],

        'region_levels' => ['province', 'regency', 'district'],

        'region_level_labels' => [
            'province' => 'Provinsi',
            'regency' => 'Kabupaten / Kota',
            'district' => 'Kecamatan',
        ],

        'per_page' => 15,

        'per_page_max' => 100,

        'upsert_chunk' => 500,

        'risk_factors' => [],

        'impact_indicators' => [],

        'data_sources' => [],

        'ai_recommendation' => [],

        'methodology_notes' => [],
    ];

    /** @var array<string, mixed> */
    protected array $config;

    /** @var array<string, bool>|null cache pemeriksaan ketersediaan dataset */
    protected ?array $sourceAvailability = null;

    /**
     * @param  array<string, mixed>|null  $config  null = baca config('sigma_priority')
     */
    public function __construct(?array $config = null)
    {
        $incoming = $config ?? (array) config('sigma_priority', []);

        $this->config = array_replace_recursive(self::DEFAULT_CONFIG, $incoming);
    }

    /* ==================================================================
     | KONFIGURASI & METADATA
     | ================================================================== */

    public function methodologyVersion(): string
    {
        return (string) ($this->config['methodology_version'] ?? 'priority-hybrid-v1.0');
    }

    /**
     * Bobot komponen, dinormalisasi agar totalnya 1.
     *
     * @return array{risk: float, impact: float}
     */
    public function weights(): array
    {
        $weights = [
            'risk' => (float) ($this->config['weights']['risk'] ?? 0.0),
            'impact' => (float) ($this->config['weights']['impact'] ?? 0.0),
        ];

        $total = $weights['risk'] + $weights['impact'];

        if ($total <= 0) {
            return ['risk' => 0.0, 'impact' => 0.0];
        }

        return [
            'risk' => round($weights['risk'] / $total, 4),
            'impact' => round($weights['impact'] / $total, 4),
        ];
    }

    /**
     * Daftar kategori prioritas sesuai urutan pemeriksaan (skor tertinggi dulu).
     *
     * @return array<string, array<string, mixed>>
     */
    public function levels(): array
    {
        $levels = [];

        foreach ($this->levelOrder() as $key) {

            $meta = $this->config['levels'][$key] ?? null;

            if (! is_array($meta)) {
                continue;
            }

            $levels[$key] = $meta + [
                'threshold' => 0,
                'label' => ucfirst($key),
                'badge' => '',
                'accent' => '',
                'note' => '',
            ];

        }

        return $levels;
    }

    /**
     * @return array<int, string>
     */
    public function levelOrder(): array
    {
        return array_values((array) ($this->config['level_order'] ?? []));
    }

    public function levelMeta(?string $level): ?array
    {
        return $level === null ? null : ($this->levels()[$level] ?? null);
    }

    public function levelLabel(?string $level): string
    {
        return (string) ($this->levelMeta($level)['label'] ?? 'Belum dihitung');
    }

    public function levelBadge(?string $level): string
    {
        return (string) ($this->levelMeta($level)['badge'] ?? '');
    }

    public function levelAccent(?string $level): string
    {
        return (string) ($this->levelMeta($level)['accent'] ?? 'orange');
    }

    public function levelNote(?string $level): string
    {
        return (string) ($this->levelMeta($level)['note'] ?? '');
    }

    /**
     * Kategori prioritas dari sebuah skor (null bila skor belum dihitung).
     */
    public function levelFromScore(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        $levels = $this->levels();

        foreach ($levels as $key => $meta) {
            if ($score >= (float) $meta['threshold']) {
                return $key;
            }
        }

        $keys = array_keys($levels);

        return end($keys) ?: null;
    }

    /**
     * @return array<int, string>
     */
    public function regionLevels(): array
    {
        return array_values((array) ($this->config['region_levels'] ?? []));
    }

    public function regionLevelLabel(?string $level): string
    {
        if ($level === null || $level === '') {
            return '';
        }

        return (string) ($this->config['region_level_labels'][$level] ?? ucfirst($level));
    }

    public function perPage(): int
    {
        $perPage = (int) ($this->config['per_page'] ?? 15);

        $max = (int) ($this->config['per_page_max'] ?? 100);

        return max(1, min($perPage, $max));
    }

    /**
     * @return array<int, string>
     */
    public function methodologyNotes(): array
    {
        return array_values((array) ($this->config['methodology_notes'] ?? []));
    }

    /**
     * Label komponen (Risiko / Dampak) beserta model/dataset asalnya.
     *
     * @return array{label: string, label_long: string, source: string, source_detail: string}
     */
    public function componentMeta(string $component): array
    {
        $meta = (array) ($this->config['components'][$component] ?? []);

        return [
            'label' => (string) ($meta['label'] ?? ucfirst($component)),
            'label_long' => (string) ($meta['label_long'] ?? ucfirst($component)),
            'source' => (string) ($meta['source'] ?? ''),
            'source_detail' => (string) ($meta['source_detail'] ?? ''),
        ];
    }

    /* ==================================================================
     | PERHITUNGAN SKOR (murni, tanpa akses database)
     | ================================================================== */

    /**
     * Hitung Priority Score dari Risk Score dan Impact Score.
     *
     * Bobot komponen yang datanya belum tersedia dikeluarkan, lalu bobot
     * komponen tersisa dinormalisasi ulang (lihat catatan kelas).
     *
     * @return array{
     *     score: float|null,
     *     level: string|null,
     *     level_label: string,
     *     badge: string,
     *     data_completeness: float,
     *     weights: array<string, float>,
     *     effective_weights: array<string, float>,
     *     components: array<string, array<string, mixed>>,
     *     unavailable: array<int, string>
     * }
     */
    public function calculate(?float $riskScore, ?float $impactScore): array
    {
        $weights = $this->weights();

        $values = [
            'risk' => $riskScore,
            'impact' => $impactScore,
        ];

        $availableWeight = 0.0;

        foreach ($values as $key => $value) {
            if ($value !== null) {
                $availableWeight += $weights[$key];
            }
        }

        $totalWeight = $weights['risk'] + $weights['impact'];

        /* Bobot efektif hanya untuk komponen yang datanya tersedia */
        $effectiveWeights = [];
        $score = 0.0;

        if ($availableWeight > 0) {

            foreach ($values as $key => $value) {

                if ($value === null) {
                    continue;
                }

                $effectiveWeights[$key] = round($weights[$key] / $availableWeight, 4);

                $score += (float) $value * $effectiveWeights[$key];

            }

            $score = round($score, 2);
        }

        $components = [
            'risk' => $this->component('risk', $riskScore, $weights['risk'], $effectiveWeights['risk'] ?? null),
            'impact' => $this->component('impact', $impactScore, $weights['impact'], $effectiveWeights['impact'] ?? null),
        ];

        $unavailable = [];

        foreach ($values as $key => $value) {
            if ($value === null) {
                $unavailable[] = $key;
            }
        }

        $hasScore = $availableWeight > 0;

        $level = $hasScore ? $this->levelFromScore($score) : null;

        return [
            'score' => $hasScore ? $score : null,
            'level' => $level,
            'level_label' => $this->levelLabel($level),
            'badge' => $this->levelBadge($level),
            'data_completeness' => $totalWeight > 0
                ? round($availableWeight / $totalWeight * 100, 2)
                : 0.0,
            'weights' => $weights,
            'effective_weights' => $effectiveWeights,
            'components' => $components,
            'unavailable' => $unavailable,
        ];
    }

    /**
     * Rincian satu komponen skor.
     *
     * @return array<string, mixed>
     */
    protected function component(
        string $key,
        ?float $value,
        float $weight,
        ?float $effectiveWeight
    ): array {
        return [
            'key' => $key,
            'label' => $this->componentMeta($key)['label'],
            'source' => $this->componentMeta($key)['source'],
            'available' => $value !== null,
            'raw' => $value,
            'weight' => $weight,
            'effective_weight' => $effectiveWeight,
            'contribution' => ($value !== null && $effectiveWeight !== null)
                ? round($value * $effectiveWeight, 2)
                : null,
        ];
    }

    /* ==================================================================
     | SUMBER DATA (diprobe langsung dari database)
     | ================================================================== */

    /**
     * Status ketersediaan seluruh dataset masukan modul.
     *
     * Dipakai UI untuk menampilkan "Data belum tersedia" secara jujur dan
     * sebagai bukti kesiapan modul menerima dataset baru: menambahkan dataset
     * cukup dengan mengisi "probe" di config/sigma_priority.php.
     *
     * @return array<string, array<string, mixed>>
     */
    public function dataSources(): array
    {
        $availability = $this->sourceAvailability();

        $sources = [];

        foreach ((array) $this->config['data_sources'] as $key => $meta) {

            $meta = (array) $meta;

            $available = (bool) ($availability[$key] ?? false);

            $sources[$key] = [
                'key' => $key,
                'label' => (string) ($meta['label'] ?? $key),
                'available' => $available,
                'status' => $available ? 'available' : 'unavailable',
                'note' => (string) ($meta['note'] ?? ''),
                'role' => (string) ($meta['role'] ?? ''),
            ];

        }

        return $sources;
    }

    /**
     * Ringkas status beberapa dataset sekaligus (untuk masukan AI).
     *
     * @param  array<int, string>  $keys
     * @return array<string, array<string, mixed>>
     */
    public function datasetStates(array $keys, ?array $sources = null): array
    {
        $sources ??= $this->dataSources();

        $states = [];

        foreach ($keys as $key) {

            $states[$key] = $sources[$key] ?? [
                'key' => $key,
                'label' => $key,
                'available' => false,
                'status' => 'unavailable',
                'note' => '',
                'role' => '',
            ];

        }

        return $states;
    }

    /**
     * Ketersediaan dataset (hasil pemeriksaan nyata ke database).
     *
     * @return array<string, bool>
     */
    protected function sourceAvailability(): array
    {
        if ($this->sourceAvailability !== null) {
            return $this->sourceAvailability;
        }

        $availability = [];

        foreach ((array) $this->config['data_sources'] as $key => $meta) {

            /* Nilai probe dapat berupa nama tabel (string) atau definisi array */
            $availability[$key] = $this->probeSource($meta['probe'] ?? null);

        }

        return $this->sourceAvailability = $availability;
    }

    /**
     * Periksa satu definisi probe pada config.
     *
     * @param  array<string, mixed>|string|null  $probe
     */
    protected function probeSource(array|string|null $probe): bool
    {
        if (is_string($probe) && $probe !== '') {
            $probe = ['table' => $probe];
        }

        $table = is_array($probe) ? ($probe['table'] ?? null) : null;

        if (! is_string($table) || $table === '') {
            return false;
        }

        try {

            $query = DB::table($table);

            $column = $probe['column'] ?? null;

            if (is_string($column) && $column !== '') {
                $query->whereNotNull($column);
            }

            return $query->limit(1)->exists();

        } catch (\Throwable $e) {

            /* Tabel belum ada pada instalasi ini: laporkan sebagai belum tersedia */
            Log::warning('Probe sumber data prioritas gagal', [
                'table' => $table,
                'message' => $e->getMessage(),
            ]);

            return false;

        }
    }

    /* ==================================================================
     | RINGKASAN (dihitung dari database, bukan dari koleksi halaman)
     | ================================================================== */

    /**
     * Statistik ringkasan untuk Summary Card.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $counts = PriorityResult::query()
            ->selectRaw('priority_level, COUNT(*) AS total')
            ->groupBy('priority_level')
            ->pluck('total', 'priority_level');

        $byLevel = [];

        foreach ($this->levelOrder() as $level) {
            $byLevel[$level] = (int) ($counts[$level] ?? 0);
        }

        $analyzed = (int) $counts->sum();

        $totalRegions = $this->regionQuery()->count();

        $average = PriorityResult::query()->avg('priority_score');

        $lastCalculatedAt = PriorityResult::query()->max('calculated_at');

        return [
            'total_regions' => $totalRegions,
            'analyzed' => $analyzed,
            'not_analyzed' => max($totalRegions - $analyzed, 0),
            'levels' => $byLevel,
            'average_score' => $average !== null ? round((float) $average, 2) : null,
            'last_calculated_at' => $lastCalculatedAt ? Carbon::parse($lastCalculatedAt) : null,
            'methodology_version' => $this->methodologyVersion(),
        ];
    }

    /* ==================================================================
     | RANKING (filter & pagination dikerjakan oleh database)
     | ================================================================== */

    /**
     * Query tabel ranking: priority_score DESC, nama wilayah ASC pemecah seri.
     */
    public function rankingQuery(array $filters = []): Builder
    {
        $query = PriorityResult::query()
            ->join('regions', 'regions.id', '=', 'priority_results.region_id')
            ->leftJoin('regions as parent_regions', 'parent_regions.id', '=', 'regions.parent_id')
            ->select([
                'priority_results.id',
                'priority_results.region_id',
                'priority_results.risk_score',
                'priority_results.impact_score',
                'priority_results.priority_score',
                'priority_results.priority_level',
                'priority_results.ranking_position',
                'priority_results.data_completeness',
                'priority_results.calculated_at',
                'regions.name as region_name',
                'regions.level as region_level',
                'regions.code as region_code',
                'parent_regions.name as parent_region_name',
                'parent_regions.level as parent_region_level',
            ])
            ->orderByDesc('priority_results.priority_score')
            ->orderBy('regions.name');

        return $this->applyFilters($query, $filters);
    }

    public function ranking(array $filters = []): LengthAwarePaginator
    {
        return $this->rankingQuery($filters)
            ->paginate($this->perPage())
            ->withQueryString();
    }

    /**
     * Filter pencarian wilayah, kategori prioritas, dan wilayah administratif
     * (provinsi / kabupaten) — seluruhnya dikerjakan sebagai query database.
     *
     * @param  Builder  $query
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {

            $query->where(function (Builder $sub) use ($search) {
                $sub->where('regions.name', 'like', '%' . $search . '%')
                    ->orWhere('parent_regions.name', 'like', '%' . $search . '%');
            });

        }

        if (! empty($filters['level'])) {
            $query->where('priority_results.priority_level', $filters['level']);
        }

        $regencyId = $filters['regency_id'] ?? null;

        $provinceId = $filters['province_id'] ?? null;

        if ($regencyId) {

            /* Kabupaten/kota terpilih beserta kecamatan di dalamnya */
            $query->where(function (Builder $sub) use ($regencyId) {
                $sub->where('regions.id', $regencyId)
                    ->orWhere('regions.parent_id', $regencyId);
            });

        } elseif ($provinceId) {

            /* Provinsi terpilih beserta kabupaten dan kecamatan di dalamnya */
            $query->where(function (Builder $sub) use ($provinceId) {
                $sub->where('regions.id', $provinceId)
                    ->orWhere('regions.parent_id', $provinceId)
                    ->orWhereIn(
                        'regions.parent_id',
                        fn ($inner) => $inner->select('id')->from('regions')->where('parent_id', $provinceId)
                    );
            });

        }

        return $query;
    }

    /**
     * Pilihan filter: provinsi, kabupaten (mengikuti provinsi terpilih), dan
     * kategori prioritas. Semua berasal dari database.
     *
     * @return array<string, mixed>
     */
    public function filterOptions(array $filters = []): array
    {
        $provinceId = $filters['province_id'] ?? null;

        $levels = [];

        foreach ($this->levels() as $key => $meta) {
            $levels[$key] = $meta + ['key' => $key];
        }

        return [
            'provinces' => Region::query()
                ->where('level', 'province')
                ->orderBy('name')
                ->get(['id', 'name']),

            /* Kabupaten hanya dimuat bila provinsi sudah dipilih */
            'regencies' => $provinceId
                ? Region::query()
                    ->where('parent_id', $provinceId)
                    ->orderBy('name')
                    ->get(['id', 'name'])
                : collect(),

            'levels' => $levels,
        ];
    }

    /* ==================================================================
     | DETAIL WILAYAH
     | ================================================================== */

    /**
     * Susun seluruh data halaman detail prioritas satu wilayah.
     *
     * Semua bagian selalu ada; bagian yang datanya belum tersedia ditandai
     * available = false dengan nilai null supaya UI menampilkan "Data belum
     * tersedia" alih-alih angka kosong.
     *
     * @return array<string, mixed>
     */
    public function detail(PriorityResult $result): array
    {
        $result->loadMissing('region.parent');

        $region = $result->region;

        $risk = $region ? $this->latestRisk($region->id) : null;

        $impact = $region ? $this->latestImpact($region->id) : null;

        $center = $region ? RegionGeometry::centerForRegion($region) : null;

        $sources = $this->dataSources();

        return [
            'result' => $result,

            'region' => [
                'id' => $region?->id,
                'name' => $region?->name,
                'code' => $region?->code,
                'level' => $region?->level,
                'level_label' => $this->regionLevelLabel($region?->level),
                'parent_name' => $region?->parent?->name,
                'parent_level_label' => $this->regionLevelLabel($region?->parent?->level),
            ],

            'coordinates' => [
                'available' => $center !== null,
                'latitude' => $center['latitude'] ?? null,
                'longitude' => $center['longitude'] ?? null,
                'source' => 'Titik tengah geometri wilayah (GIS Database)',
            ],

            'risk' => [
                'available' => $result->risk_score !== null,
                'score' => $result->risk_score,
                'level' => $risk?->risk_level,
                'calculated_at' => $risk?->calculated_at,
                'source' => $this->componentMeta('risk'),
                'factors' => $this->riskFactors($risk),
                'weather' => $this->weatherContext($risk, $sources),
            ],

            'impact' => [
                'available' => $result->impact_score !== null,
                'score' => $result->impact_score,
                'level' => $impact?->impact_level,
                'calculated_at' => $impact?->calculated_at,
                'methodology_version' => $impact?->methodology_version,
                'radius_km' => $impact?->analysis_radius_km,
                'source' => $this->componentMeta('impact'),
                'components' => $this->impactComponents($impact),
                'indicators' => $this->impactIndicators($impact),
            ],

            'priority' => [
                'score' => $result->priority_score,
                'level' => $result->priority_level,
                'level_label' => $this->levelLabel($result->priority_level),
                'level_note' => $this->levelNote($result->priority_level),
                'badge' => $this->levelBadge($result->priority_level),
                'data_completeness' => $result->data_completeness,
                'ranking_position' => $result->ranking_position,
                'components' => (array) ($result->components ?? []),
                'calculated_at' => $result->calculated_at,
                'methodology_version' => $result->methodology_version,
            ],

            'sources' => $sources,

            'methodology_notes' => $this->methodologyNotes(),
        ];
    }

    /**
     * Analisis risiko terbaru sebuah wilayah (satu baris per wilayah).
     */
    public function latestRisk(int $regionId): ?FireRisk
    {
        return FireRisk::query()
            ->where('region_id', $regionId)
            ->orderByDesc('calculated_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Analisis dampak terbaru sebuah wilayah.
     *
     * Hanya analisis yang dihasilkan engine analisis dampak
     * (punya methodology_version) yang dipakai supaya skor tidak tercampur
     * dengan data lama tanpa metodologi.
     */
    public function latestImpact(int $regionId): ?ImpactAssessment
    {
        return ImpactAssessment::query()
            ->where('region_id', $regionId)
            ->whereNotNull('methodology_version')
            ->orderByDesc('calculated_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Faktor penyebab dari sisi risiko (kolom fire_risks).
     *
     * @return array<string, array<string, mixed>>
     */
    public function riskFactors(?FireRisk $risk): array
    {
        $factors = [];

        foreach ((array) $this->config['risk_factors'] as $column => $meta) {

            $meta = (array) $meta;

            $value = $risk?->{$column};

            $available = $value !== null && $value !== '';

            $factors[$column] = [
                'key' => $column,
                'label' => (string) ($meta['label'] ?? $column),
                'available' => $available,
                'value' => $available
                    ? round((float) $value, (int) ($meta['decimals'] ?? 1))
                    : null,
                'unit' => $meta['unit'] ?? null,
                'influence' => $meta['influence'] ?? null,
            ];

        }

        return $factors;
    }

    /**
     * Rincian komponen skor dampak (dari kolom JSON impact_assessments.components).
     *
     * @return array<string, array<string, mixed>>
     */
    public function impactComponents(?ImpactAssessment $impact): array
    {
        $components = (array) ($impact?->components ?? []);

        $result = [];

        foreach ($components as $key => $component) {

            if (! is_array($component)) {
                continue;
            }

            $result[$key] = [
                'key' => $key,
                'label' => (string) ($component['label'] ?? $key),
                'available' => (bool) ($component['available'] ?? false),
                'value' => $component['normalized'] ?? null,
                'weight' => $component['weight'] ?? null,
                'contribution' => $component['contribution'] ?? null,
                'note' => $component['note'] ?? null,
            ];

        }

        return $result;
    }

    /**
     * Indikator dampak mentah (kolom impact_assessments).
     *
     * @return array<string, array<string, mixed>>
     */
    public function impactIndicators(?ImpactAssessment $impact): array
    {
        $indicators = [];

        foreach ((array) $this->config['impact_indicators'] as $column => $meta) {

            $meta = (array) $meta;

            $value = $impact?->{$column};

            $available = $value !== null;

            $indicators[$column] = [
                'key' => $column,
                'label' => (string) ($meta['label'] ?? $column),
                'available' => $available,
                'value' => $available
                    ? round((float) $value, (int) ($meta['decimals'] ?? 0))
                    : null,
                'unit' => $meta['unit'] ?? null,
            ];

        }

        return $indicators;
    }

    /**
     * Konteks cuaca: parameter yang dipakai model risiko + status dataset
     * cuaca historis di database.
     *
     * @param  array<string, mixed>|null  $sources
     * @return array<string, mixed>
     */
    public function weatherContext(?FireRisk $risk, ?array $sources = null): array
    {
        $sources ??= $this->dataSources();

        $values = [
            'temperature' => $risk?->temperature,
            'humidity' => $risk?->humidity,
            'rainfall' => $risk?->rainfall,
            'wind_speed' => $risk?->wind_speed,
        ];

        $hasValue = false;

        foreach ($values as $key => $value) {
            $values[$key] = $value === null ? null : (float) $value;
            $hasValue = $hasValue || $values[$key] !== null;
        }

        return $values + [
            'available' => $hasValue,
            'recorded_dataset_available' => (bool) ($sources['weather']['available'] ?? false),
            'source' => 'Parameter cuaca pada fire_risks (masukan model risiko AI Service)',
        ];
    }

    /* ==================================================================
     | PERHITUNGAN ULANG (Priority Calculation Engine)
     | ================================================================== */

    /**
     * Hitung ulang prioritas wilayah lalu simpan ke tabel priority_results.
     *
     * Dipakai tombol "Hitung Ulang Prioritas" dan command
     * `php artisan priority:recalculate` (dapat dijadwalkan setelah dataset
     * AI/GIS baru selesai diimpor).
     *
     * @param  array<int, int>|null  $regionIds  null = seluruh wilayah
     * @return array<string, mixed>
     */
    public function recalculate(?array $regionIds = null): array
    {
        $startedAt = microtime(true);

        $calculatedAt = now();

        $regions = $this->regionQuery($regionIds)
            ->select(['id', 'name', 'level', 'parent_id'])
            ->get();

        if ($regions->isEmpty()) {

            return [
                'processed' => 0,
                'skipped' => 0,
                'levels' => [],
                'top' => null,
                'duration_seconds' => 0.0,
                'calculated_at' => $calculatedAt,
                'methodology_version' => $this->methodologyVersion(),
                'message' => 'Tidak ada wilayah yang dapat dihitung.',
            ];

        }

        $ids = $regions->pluck('id')->all();

        $risks = $this->latestPerRegion(
            FireRisk::class,
            $ids,
            ['id', 'region_id', 'risk_score', 'calculated_at']
        );

        $impacts = $this->latestPerRegion(
            ImpactAssessment::class,
            $ids,
            ['id', 'region_id', 'impact_score', 'calculated_at'],
            fn ($query) => $query->whereNotNull('methodology_version')
        );

        $rows = [];

        $withoutData = [];

        foreach ($regions as $region) {

            $risk = $risks->get($region->id);

            $impact = $impacts->get($region->id);

            $riskScore = $risk?->risk_score !== null ? (float) $risk->risk_score : null;

            $impactScore = $impact?->impact_score !== null ? (float) $impact->impact_score : null;

            /* Wilayah tanpa data risiko maupun dampak tidak diranking */
            if ($riskScore === null && $impactScore === null) {
                $withoutData[] = $region->id;
                continue;
            }

            $calculation = $this->calculate($riskScore, $impactScore);

            $rows[] = [
                'region_id' => $region->id,
                'risk_score' => $riskScore,
                'impact_score' => $impactScore,
                'priority_score' => $calculation['score'],
                'priority_level' => $calculation['level'],
                'data_completeness' => $calculation['data_completeness'],
                'components' => json_encode($calculation['components'], JSON_UNESCAPED_UNICODE),
                'sources' => json_encode($this->sourceSnapshot($risk, $impact), JSON_UNESCAPED_UNICODE),
                'methodology_version' => $this->methodologyVersion(),
                'risk_calculated_at' => $risk?->calculated_at,
                'impact_calculated_at' => $impact?->calculated_at,
                'calculated_at' => $calculatedAt,
                'created_at' => $calculatedAt,
                'updated_at' => $calculatedAt,

                /* Hanya dipakai sebagai pemecah seri, tidak ikut disimpan */
                '_region_name' => $region->name,
            ];

        }

        $this->assignRanking($rows);

        $this->persist($rows, $withoutData);

        $levels = [];

        foreach ($rows as $row) {
            $level = $row['priority_level'];
            $levels[$level] = ($levels[$level] ?? 0) + 1;
        }

        return [
            'processed' => count($rows),
            'skipped' => count($withoutData),
            'levels' => $levels,
            'top' => $rows[0] ?? null,
            'duration_seconds' => round(microtime(true) - $startedAt, 2),
            'calculated_at' => $calculatedAt,
            'methodology_version' => $this->methodologyVersion(),
            'message' => 'Prioritas dihitung ulang untuk ' . count($rows) . ' wilayah.',
        ];
    }

    /**
     * Urutkan hasil (priority_score DESC, nama wilayah ASC) lalu tetapkan
     * peringkat nasional.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function assignRanking(array &$rows): void
    {
        usort($rows, function (array $a, array $b): int {

            $score = (float) ($b['priority_score'] ?? 0) <=> (float) ($a['priority_score'] ?? 0);

            if ($score !== 0) {
                return $score;
            }

            return strcmp((string) $a['_region_name'], (string) $b['_region_name']);

        });

        foreach ($rows as $index => $row) {
            $rows[$index]['ranking_position'] = $index + 1;
        }
    }

    /**
     * Simpan hasil perhitungan (upsert per potongan) dalam satu transaksi,
     * lalu bersihkan baris wilayah yang datanya sudah tidak ada lagi.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, int>  $withoutData
     */
    protected function persist(array $rows, array $withoutData): void
    {
        $chunkSize = max(1, (int) ($this->config['upsert_chunk'] ?? 500));

        $columns = [
            'risk_score',
            'impact_score',
            'priority_score',
            'priority_level',
            'ranking_position',
            'data_completeness',
            'components',
            'sources',
            'methodology_version',
            'risk_calculated_at',
            'impact_calculated_at',
            'calculated_at',
            'updated_at',
        ];

        DB::transaction(function () use ($rows, $withoutData, $chunkSize, $columns) {

            foreach (array_chunk($rows, $chunkSize) as $chunk) {

                PriorityResult::query()->upsert(
                    array_map(fn (array $row) => Arr::except($row, ['_region_name']), $chunk),
                    ['region_id'],
                    $columns
                );

            }

            if ($withoutData !== []) {
                PriorityResult::query()->whereIn('region_id', $withoutData)->delete();
            }

        });
    }

    /**
     * Snapshot sumber data yang dipakai saat perhitungan (jejak audit).
     *
     * @return array<string, mixed>
     */
    protected function sourceSnapshot(?FireRisk $risk, ?ImpactAssessment $impact): array
    {
        return [
            'risk' => [
                'available' => $risk !== null,
                'source' => $this->componentMeta('risk')['source'],
                'calculated_at' => $risk?->calculated_at?->toDateTimeString(),
            ],
            'impact' => [
                'available' => $impact !== null,
                'source' => $this->componentMeta('impact')['source'],
                'calculated_at' => $impact?->calculated_at?->toDateTimeString(),
            ],
        ];
    }

    /**
     * Query wilayah yang dihitung (dibatasi level wilayah pada config).
     *
     * @param  array<int, int>|null  $regionIds
     */
    protected function regionQuery(?array $regionIds = null): Builder
    {
        return Region::query()
            ->whereIn('level', $this->regionLevels())
            ->when(
                $regionIds !== null && $regionIds !== [],
                fn (Builder $query) => $query->whereIn('id', $regionIds)
            );
    }

    /**
     * Data terbaru per wilayah dari satu tabel analisis.
     *
     * Urutan naik (ascending) + keyBy('region_id') membuat baris TERAKHIR
     * menimpa baris sebelumnya, sehingga hasilnya adalah baris paling baru.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  array<int, int>  $regionIds
     * @param  array<int, string>  $columns
     */
    protected function latestPerRegion(
        string $model,
        array $regionIds,
        array $columns,
        ?Closure $constraint = null
    ): Collection {
        $query = $model::query()
            ->whereIn('region_id', $regionIds)
            ->orderBy('calculated_at')
            ->orderBy('id')
            ->select($columns);

        if ($constraint !== null) {
            $constraint($query);
        }

        return $query->get()->keyBy('region_id');
    }
}
