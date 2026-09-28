@extends('layouts.government')

@section('title', 'Risiko Karhutla | SIGMA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/government/fire-risk.css') }}?v={{ time() }}">
@endpush

@section('content')

@php
    $riskByRegion = $fireRisks->keyBy('region_id');

    $provinces = $regions
        ->where('level', 'province')
        ->sortBy('name')
        ->values();

    $selectedRegion = $provinces->first();
    $selectedRisk = $selectedRegion
        ? $riskByRegion->get($selectedRegion->id)
        : null;

    /*
    |--------------------------------------------------------------------------
    | $sigmaRegions sudah dikirim oleh controller
    |--------------------------------------------------------------------------
    |
    | Jangan membangun ulang dari $region->geometry. Accessor tersebut
    | menjalankan 1 query + json_decode untuk setiap wilayah, sehingga
    | halaman kehabisan memory (7231 wilayah) dan berhenti dengan HTTP 500.
    |
    */
@endphp

<section class="page-heading">
    <div>
        <p class="breadcrumb">Beranda › Risiko Karhutla</p>
        <h1>Analisis Risiko Karhutla</h1>
        <p>Analisis tingkat risiko kebakaran di setiap wilayah.</p>
    </div>

    <button class="button button-light" data-ai-refresh>
        Perbarui Analisis
    </button>
</section>

<div class="government-grid risk-grid">

    <section class="panel map-panel">
        <div id="sigma-map"></div>

        <div class="map-legend">
            <span class="success">● Rendah</span>
            <span class="warning">● Sedang</span>
            <span class="orange-text">● Tinggi</span>
            <span class="danger">● Ekstrem</span>
        </div>
    </section>

    <aside class="panel detail-panel">
        <h3>Detail Wilayah</h3>

        <div class="region-selector">

            <label for="province-select">
                Provinsi
            </label>

            <select id="province-select">
                <option value="">Pilih Provinsi</option>

                @foreach ($provinces as $province)
                    <option value="{{ $province->id }}">
                        {{ $province->name }}
                    </option>
                @endforeach
            </select>

            <label for="regency-select">
                Kabupaten / Kota
            </label>

            <select id="regency-select" disabled>
                <option value="">
                    Pilih Kabupaten / Kota
                </option>
            </select>

            <label for="district-select">
                Kecamatan
            </label>

            <select id="district-select" data-region-select disabled>
                <option value="">
                    Pilih Kecamatan
                </option>
            </select>

        </div>

        <div class="score-ring">
            <b id="risk-score">
                {{ $selectedRisk?->risk_score ?? 0 }}
            </b>

            <span>/100</span>
        </div>

        <p class="score-label">
            Risiko

            <b id="risk-level">
                {{ $selectedRisk?->risk_level ?? 'LOW' }}
            </b>
        </p>

        <div class="parameter">
            <span>Suhu</span>

            <b id="risk-temperature">
                {{ $selectedRisk?->temperature !== null ? $selectedRisk->temperature . '°C' : '-' }}
            </b>
        </div>

        <div class="parameter">
            <span>Kelembapan</span>

            <b id="risk-humidity">
                {{ $selectedRisk?->humidity !== null ? $selectedRisk->humidity . '%' : '-' }}
            </b>
        </div>

        <div class="parameter">
            <span>Kecepatan Angin</span>

            <b id="risk-wind">
                {{ $selectedRisk?->wind_speed !== null ? $selectedRisk->wind_speed . ' km/jam' : '-' }}
            </b>
        </div>

        <div class="parameter">
            <span>Curah Hujan</span>

            <b id="risk-rainfall">
                {{ $selectedRisk?->rainfall !== null ? $selectedRisk->rainfall . ' mm' : '-' }}
            </b>
        </div>
    </aside>

</div>

<script>
    window.regionChildrenUrl = "{{ url('/regions') }}";
    window.fireRiskRefreshUrl = "{{ route('government.fire-risk.ai-refresh') }}";
    window.sigmaRegions = @json($sigmaRegions);
</script>

@push('scripts')
<script src="{{ asset('js/government/gis-map.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/government/fire-risk.js') }}?v={{ time() }}"></script>
@endpush

@endsection
