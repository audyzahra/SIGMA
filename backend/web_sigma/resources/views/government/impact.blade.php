@extends('layouts.government')

@section('title', 'Analisis Dampak | SIGMA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/government/impact.css') }}?v={{ time() }}">
@endpush

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Analisis awal
    |--------------------------------------------------------------------------
    |
    | $analysis hanya terisi bila halaman dibuka dengan ?region_id=,
    | ?hotspot_id=, atau ?incident_id=. Semua angkanya berasal dari
    | ImpactAnalysisService (perhitungan spatial MySQL + data nyata).
    |
    */

    $metrics = $analysis['metrics'] ?? $metricCatalog;
    $scoreData = $analysis['impact_score'] ?? null;
    $affected = $analysis['affected_regions'] ?? null;
    $riskContext = $analysis['risk'] ?? null;
    $hotspotContext = $analysis['hotspots'] ?? null;
    $incidentContext = $analysis['incidents'] ?? null;
    $reportContext = $analysis['reports'] ?? null;
    $center = $analysis['center'] ?? null;
    $focalRegion = $analysis['region'] ?? null;

    $levelLabels = [
        'critical' => 'Sangat Berdampak',
        'high' => 'Berdampak Tinggi',
        'medium' => 'Berdampak Sedang',
        'low' => 'Berdampak Rendah',
    ];

    $componentLabels = [
        'hotspot_intensity' => 'Intensitas Hotspot',
        'fire_risk_context' => 'Risiko Wilayah Terdampak',
        'administrative_exposure' => 'Sebaran Wilayah Terdampak',
        'incident_pressure' => 'Tekanan Insiden',
    ];

    $metricIcons = [
        'affected_area' => '▦',
        'affected_districts' => '◈',
        'affected_regencies' => '◫',
        'hotspot_count' => '♨',
        'incident_count' => '⌁',
        'report_count' => '▤',
        'weighted_risk_score' => '◉',
        'population_affected' => '♙',
        'affected_households' => '⌂',
        'school_count' => '▰',
        'hospital_count' => '✚',
        'road_distance' => '↔',
        'forest_area' => '♠',
        'peatland_area' => '▩',
    ];

    /* jumlah desimal per metrik (dikirim juga ke JS) */
    $metricDecimals = [
        'affected_area' => 2,
        'forest_area' => 2,
        'peatland_area' => 2,
        'road_distance' => 2,
        'weighted_risk_score' => 2,
    ];

    $formatMetric = function (array $metric) use ($metricDecimals): string {
        if (! $metric['available'] || $metric['value'] === null) {
            return 'Data tidak tersedia';
        }

        $decimals = $metricDecimals[$metric['key']] ?? 0;

        return number_format((float) $metric['value'], $decimals, ',', '.')
            . ($metric['unit'] ? ' ' . $metric['unit'] : '');
    };

    $adminLevelLabels = [
        'province' => 'Provinsi',
        'regency' => 'Kabupaten / Kota',
        'district' => 'Kecamatan',
    ];
@endphp

<section class="page-heading">
    <div>
        <p class="breadcrumb">Beranda › Analisis Dampak</p>

        <h1>Analisis Dampak</h1>

        <p>
            Perhitungan spatial wilayah yang berpotensi terdampak bila terjadi
            karhutla pada satu titik, beserta hotspot, insiden, dan laporan di
            sekitarnya.
        </p>
    </div>

    <div class="heading-actions">
        <button type="button" class="button button-light" data-impact-save>
            Simpan Hasil
        </button>
    </div>
</section>

{{-- FILTER TITIK ANALISIS --}}
<section class="panel impact-filter">
    <div class="filters">
        <select id="impact-province" aria-label="Provinsi">
            <option value="">Pilih Provinsi</option>

            @foreach ($provinces as $province)
                <option value="{{ $province->id }}">{{ $province->name }}</option>
            @endforeach
        </select>

        <select id="impact-regency" aria-label="Kabupaten / Kota" disabled>
            <option value="">Pilih Kabupaten / Kota</option>
        </select>

        <select id="impact-district" aria-label="Kecamatan" disabled>
            <option value="">Pilih Kecamatan</option>
        </select>

        <select id="impact-radius" aria-label="Radius analisis">
            @foreach ($radiusOptions as $option)
                <option value="{{ $option }}" @selected((float) $option === (float) $radius)>
                    Radius {{ $option }} km
                </option>
            @endforeach
        </select>

        <select id="impact-point" aria-label="Titik cepat" class="impact-point-select">
            <option value="">Titik cepat: hotspot / insiden</option>

            @if ($hotspots->isNotEmpty())
                <optgroup label="Hotspot NASA FIRMS">
                    @foreach ($hotspots as $hotspot)
                        <option value="hotspot:{{ $hotspot->id }}">
                            {{ $hotspot->satellite_name ?: 'Hotspot' }} ·
                            {{ number_format((float) $hotspot->latitude, 4) }},
                            {{ number_format((float) $hotspot->longitude, 4) }}
                        </option>
                    @endforeach
                </optgroup>
            @endif

            @if ($incidents->isNotEmpty())
                <optgroup label="Insiden">
                    @foreach ($incidents as $incident)
                        <option value="incident:{{ $incident->id }}">
                            {{ \Illuminate\Support\Str::limit($incident->location_description ?: 'Insiden', 40) }}
                            ·
                            {{ number_format((float) $incident->latitude, 4) }},
                            {{ number_format((float) $incident->longitude, 4) }}
                        </option>
                    @endforeach
                </optgroup>
            @endif
        </select>

        <button type="button" class="button button-primary" data-impact-run>
            Jalankan Analisis
        </button>
    </div>

    <p class="impact-filter-note">
        Pilih wilayah (provinsi → kabupaten → kecamatan) atau titik hotspot /
        insiden, lalu jalankan analisis. Wilayah terkecamatan yang dipilih
        dipakai sebagai titik analisis; klik wilayah pada peta untuk
        menganalisis ulang wilayah tersebut.
    </p>
</section>

{{-- PETA + RINGKASAN DAMPAK --}}
<div class="government-grid risk-grid">

    <section class="panel map-panel">
        <div id="sigma-impact-map"></div>

        <div class="map-legend">
            <span class="legend-fire">● Titik analisis</span>
            <span class="legend-zone">● Zona radius</span>
            <span class="legend-affected">● Wilayah terdampak</span>
            <span class="legend-hotspot">● Hotspot</span>
            <span class="legend-incident">● Insiden</span>
            <span class="legend-report">● Laporan</span>
        </div>
    </section>

    <aside class="panel impact-detail">
        <h3>Dampak Potensial</h3>

        <p class="impact-status" id="impact-status">
            @if ($analysis && ($analysis['success'] ?? false))
                Analisis dijalankan {{ $analysis['calculated_at'] }} ·
                {{ $center['source'] === 'region_centroid' ? 'titik pusat wilayah' : 'titik ' . $center['source'] }}
            @else
                Belum ada analisis. Pilih wilayah atau hotspot lalu jalankan analisis.
            @endif
        </p>

        <div class="parameter">
            <span>Titik analisis</span>
            <b id="impact-center">
                @if ($center)
                    {{ number_format($center['latitude'], 5) }},
                    {{ number_format($center['longitude'], 5) }}
                @else
                    -
                @endif
            </b>
        </div>

        <div class="parameter">
            <span>Wilayah fokus</span>
            <b id="impact-region">
                {{ $focalRegion['name'] ?? '-' }}
            </b>
        </div>

        <div class="parameter">
            <span>Radius analisis</span>
            <b id="impact-radius-label">
                {{ $center ? number_format($center['radius_km'], 2) . ' km' : '-' }}
            </b>
        </div>

        @foreach ($metrics as $metric)
            <div class="impact-metric {{ $metric['available'] ? '' : 'is-unavailable' }}"
                data-metric="{{ $metric['key'] }}"
                data-available="{{ $metric['available'] ? '1' : '0' }}">

                <span>{{ $metricIcons[$metric['key']] ?? '•' }}</span>

                <div>
                    <b data-metric-value>{{ $formatMetric($metric) }}</b>

                    <small data-metric-label>{{ $metric['label'] }}</small>

                    <em data-metric-note>{{ $metric['reason'] ?: ($metric['unit'] ?: '') }}</em>
                </div>
            </div>
        @endforeach

        <div class="impact-score">
            <span>Impact Score</span>

            <b>
                <i data-impact-score>{{ $scoreData['score'] ?? 0 }}</i>
                <small>/100</small>
            </b>

            <strong data-impact-level>
                {{ $levelLabels[$scoreData['level'] ?? 'low'] ?? 'Berdampak Rendah' }}
            </strong>
        </div>

        <div class="parameter">
            <span>Kelengkapan data</span>
            <b data-impact-completeness>
                {{ $scoreData ? number_format($scoreData['weights_used']['data_completeness_percent'], 2) . '%' : '-' }}
            </b>
        </div>
    </aside>

</div>

{{-- RINCIAN IMPACT SCORE --}}
<section class="panel impact-components">
    <div class="panel-title">
        <h3>Rincian Impact Score</h3>

        <span class="methodology-version">Metodologi: {{ $methodologyVersion }}</span>
    </div>

    <div class="component-list" id="impact-components">
        @if ($scoreData)
            @foreach ($scoreData['components'] as $key => $component)
                <div class="impact-component {{ $component['available'] ? '' : 'is-unavailable' }}"
                    data-component="{{ $key }}">

                    <div class="component-head">
                        <b>{{ $component['label'] }}</b>

                        <span data-component-value>
                            {{ $component['available']
                                ? number_format((float) $component['normalized'], 2) . '%'
                                : 'Data tidak tersedia' }}
                        </span>
                    </div>

                    <div class="component-bar">
                        <i style="width: {{ $component['available'] ? min(max((float) $component['normalized'], 0), 100) : 0 }}%"></i>
                    </div>

                    <small data-component-note>{{ $component['note'] }}</small>

                    <em>
                        bobot {{ number_format((float) $component['weight'] * 100, 0) }}% ·
                        kontribusi {{ number_format((float) $component['contribution'], 2) }} poin
                    </em>
                </div>
            @endforeach
        @else
            @foreach ($componentLabels as $key => $label)
                <div class="impact-component is-unavailable" data-component="{{ $key }}">
                    <div class="component-head">
                        <b>{{ $label }}</b>
                        <span data-component-value>Data tidak tersedia</span>
                    </div>

                    <div class="component-bar"><i style="width: 0%"></i></div>

                    <small data-component-note>Belum ada analisis dijalankan untuk titik ini.</small>
                </div>
            @endforeach
        @endif
    </div>
</section>

{{-- WILAYAH TERDAMPAK --}}
<section class="panel">
    <div class="panel-title">
        <h3>Wilayah Terdampak</h3>

        <span data-affected-summary>
            @if ($affected && ($affected['total'] ?? 0) > 0)
                {{ $affected['total'] }} wilayah ·
                {{ number_format((float) $affected['area_km2'], 2) }} km²
            @else
                Belum ada analisis
            @endif
        </span>
    </div>

    <x-sigma.data-table>
        <thead>
            <tr>
                <th>Wilayah</th>
                <th>Level</th>
                <th>Luas Wilayah</th>
                <th>Luas Terdampak</th>
                <th>Bagian Wilayah</th>
            </tr>
        </thead>

        <tbody id="impact-region-rows">
            @if ($affected && ($affected['total'] ?? 0) > 0)
                @foreach (['district', 'regency', 'province'] as $level)
                    @foreach ($affected['by_level'][$level] as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $adminLevelLabels[$level] }}</td>
                            <td>{{ number_format((float) $row['region_km2'], 2) }} km²</td>
                            <td>{{ number_format((float) $row['intersect_km2'], 4) }} km²</td>
                            <td>{{ number_format((float) $row['intersect_percent_of_region'], 2) }}%</td>
                        </tr>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="5">
                        Belum ada wilayah yang dihitung. Jalankan analisis terlebih dahulu.
                    </td>
                </tr>
            @endif
        </tbody>
    </x-sigma.data-table>
</section>

{{-- ANALISIS TERSIMPAN (hasil perhitungan nyata) --}}
<section class="panel" id="impact-saved-rows">
    <div class="panel-title">
        <h3>Analisis Tersimpan</h3>

        <span>{{ $recentAnalyses->count() }} hasil terakhir</span>
    </div>

    <x-sigma.data-table>
        <thead>
            <tr>
                <th>Wilayah / Insiden</th>
                <th>Radius</th>
                <th>Impact Score</th>
                <th>Tingkat</th>
                <th>Dihitung</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($recentAnalyses as $assessment)
                <tr>
                    <td>
                        {{ $assessment->region?->name
                            ?? $assessment->incident?->location_description
                            ?? 'Titik koordinat' }}
                    </td>

                    <td>
                        {{ $assessment->analysis_radius_km !== null
                            ? number_format((float) $assessment->analysis_radius_km, 2) . ' km'
                            : '-' }}
                    </td>

                    <td>{{ $assessment->impact_score ?? '-' }}</td>

                    <td>
                        {{ $levelLabels[$assessment->impact_level] ?? '-' }}
                    </td>

                    <td>
                        {{ $assessment->calculated_at?->format('d/m/Y H:i') ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Belum ada hasil analisis yang disimpan. Jalankan analisis
                        lalu klik “Simpan Hasil”.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-sigma.data-table>
</section>

{{-- OBJEK DI DALAM RADIUS --}}
<div class="government-grid impact-objects-grid">

    <section class="panel">
        <div class="panel-title">
            <h3>Hotspot di Radius</h3>

            <span data-hotspot-summary>
                {{ $hotspotContext ? $hotspotContext['count'] . ' titik' : 'Belum ada analisis' }}
            </span>
        </div>

        <x-sigma.data-table>
            <thead>
                <tr>
                    <th>Satelit</th>
                    <th>FRP</th>
                    <th>Suhu Kecerahan</th>
                    <th>Jarak</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody id="impact-hotspot-rows">
                @forelse ($hotspotContext['items'] ?? [] as $item)
                    <tr>
                        <td>{{ $item['satellite_name'] ?: 'Hotspot' }}</td>
                        <td>{{ $item['frp'] !== null ? number_format($item['frp'], 2) . ' MW' : '-' }}</td>
                        <td>{{ $item['brightness_temperature'] !== null ? number_format($item['brightness_temperature'], 2) . ' K' : '-' }}</td>
                        <td>{{ number_format($item['distance_km'], 2) }} km</td>
                        <td>{{ $item['status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Tidak ada hotspot di dalam radius ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>
    </section>

    <section class="panel">
        <div class="panel-title">
            <h3>Insiden &amp; Laporan di Radius</h3>

            <span data-incident-summary>
                {{ $incidentContext ? $incidentContext['count'] . ' insiden' : 'Belum ada analisis' }}
            </span>
        </div>

        <x-sigma.data-table>
            <thead>
                <tr>
                    <th>Insiden</th>
                    <th>Status Api</th>
                    <th>Severity</th>
                    <th>Jarak</th>
                </tr>
            </thead>

            <tbody id="impact-incident-rows">
                @forelse ($incidentContext['items'] ?? [] as $item)
                    <tr>
                        <td>{{ $item['location_description'] ?: 'Insiden #' . $item['id'] }}</td>
                        <td>{{ $item['fire_status'] }}</td>
                        <td>{{ $item['severity_level'] }}</td>
                        <td>{{ number_format($item['distance_km'], 2) }} km</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Tidak ada insiden di dalam radius ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>

        <h4 class="sub-heading">Laporan Masyarakat</h4>

        <x-sigma.data-table>
            <thead>
                <tr>
                    <th>Laporan</th>
                    <th>Verifikasi</th>
                    <th>Jarak</th>
                </tr>
            </thead>

            <tbody id="impact-report-rows">
                @forelse ($reportContext['items'] ?? [] as $item)
                    <tr>
                        <td>{{ \Illuminate\Support\Str::limit($item['description'] ?: 'Laporan #' . $item['id'], 48) }}</td>
                        <td>{{ $item['verification_status'] }}</td>
                        <td>{{ number_format($item['distance_km'], 2) }} km</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Tidak ada laporan masyarakat di dalam radius ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-sigma.data-table>
    </section>

</div>

{{-- TRANSPARANSI SUMBER DATA --}}
<section class="panel">
    <div class="panel-title">
        <h3>Transparansi Sumber Data</h3>

        <span>
            Metrik yang datasetnya belum tersedia dilaporkan apa adanya,
            tidak diisi angka perkiraan.
        </span>
    </div>

    <div class="source-cards impact-sources">
        @foreach ($dataSources as $key => $source)
            <article class="source-card" data-source="{{ $key }}">
                <h3>{{ $source['label'] }}</h3>

                <span class="status {{ $source['status'] === 'available' ? 'aktif' : 'pending' }}">
                    {{ $source['status'] === 'available' ? 'Tersedia' : 'Belum tersedia' }}
                </span>

                <p>{{ $source['note'] }}</p>
            </article>
        @endforeach
    </div>
</section>

{{-- METODOLOGI --}}
<section class="panel">
    <div class="panel-title">
        <h3>Metodologi Perhitungan</h3>

        <span class="methodology-version">{{ $methodologyVersion }}</span>
    </div>

    <ul class="methodology-notes">
        @foreach ($methodologyNotes as $note)
            <li>{{ $note }}</li>
        @endforeach
    </ul>

    <p class="impact-filter-note">
        Analisis Dampak menjawab “wilayah apa yang berpotensi terdampak”.
        Analisis Risiko (AI Service) menjawab “seberapa besar peluang
        terjadinya”, Prioritas menentukan urutan penanganan, dan Rekomendasi
        menyusun tindakan. Ketiganya memakai data yang sama, tetapi
        pertanyaannya berbeda.
    </p>
</section>

<script>
    window.regionChildrenUrl = "{{ url('/regions') }}";

    window.sigmaImpactEndpoints = {
        analysis: "{{ route('government.impact.analysis') }}",
        analyze: "{{ route('government.impact.analyze') }}",
    };

    window.sigmaImpactRadiusOptions = @json($radiusOptions);
    window.sigmaImpactMetricDecimals = @json($metricDecimals);
    window.sigmaImpactLevelLabels = @json($levelLabels);
    window.sigmaImpactComponentLabels = @json($componentLabels);
    window.sigmaImpactAdminLevelLabels = @json($adminLevelLabels);
    window.sigmaImpactSelected = @json($selected);
    window.sigmaImpactAnalysis = @json($analysis);
</script>

@push('scripts')
<script src="{{ asset('js/government/impact.js') }}?v={{ time() }}"></script>
@endpush

@endsection





