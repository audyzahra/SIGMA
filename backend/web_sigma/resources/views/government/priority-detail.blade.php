@extends('layouts.government')

@section('title', 'Detail Prioritas | SIGMA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/government/priority.css') }}?v={{ time() }}">
@endpush

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Detail prioritas satu wilayah
    |--------------------------------------------------------------------------
    |
    | Seluruh angka berasal dari PriorityCalculationService::detail().
    | Bagian dengan available = false dirender sebagai "Data belum tersedia"
    | (komponen <x-sigma.unavailable>), bukan angka nol.
    |
    */

    $region = $detail['region'];
    $coordinates = $detail['coordinates'];
    $risk = $detail['risk'];
    $impact = $detail['impact'];
    $priority = $detail['priority'];

    $title = $region['name'] ?: 'Wilayah';
@endphp

{{-- ============================================================
     HEADER
============================================================ --}}

<section class="page-heading priority-heading">

    <div>
        <p class="breadcrumb">
            Beranda ›
            <a href="{{ route('government.priority') }}">Prioritas Penanganan</a> ›
            Detail
        </p>

        <h1>{{ $title }}</h1>

        <p>
            {{ $region['level_label'] }}
            @if ($region['parent_name'])
                • {{ $region['parent_name'] }}
            @endif
            — rincian risiko, dampak, dan penetapan prioritas penanganan.
        </p>
    </div>

    <div class="heading-actions">

        @if ($priority['ranking_position'])
            <span class="rank-highlight">
                Peringkat #{{ $priority['ranking_position'] }}
            </span>
        @endif

        <a class="button button-light" href="{{ route('government.priority') }}">
            Kembali ke Ranking
        </a>

    </div>

</section>

{{-- ============================================================
     INFORMASI WILAYAH & SKOR
============================================================ --}}

<div class="priority-detail-grid">

    <section class="panel priority-detail-sources-panel">

        <div class="panel-title">
            <div>
                <h3>Informasi Wilayah</h3>
                <p>Identitas wilayah dan titik tengah geometri (GIS).</p>
            </div>
        </div>

        <div class="parameter">
            <span>Nama Wilayah</span>
            <b class="parameter-value">{{ $title }}</b>
        </div>

        <div class="parameter">
            <span>Region</span>
            <b class="parameter-value">
                {{ $region['level_label'] }}
                @if ($region['parent_name'])
                    • {{ $region['parent_name'] }}
                @endif
            </b>
        </div>

        <div class="parameter">
            <span>Kode Wilayah</span>
            <b class="parameter-value">
                @if ($region['code'])
                    {{ $region['code'] }}
                @else
                    <x-sigma.unavailable />
                @endif
            </b>
        </div>

        <x-sigma.parameter
            label="Koordinat (Lintang)"
            :value="$coordinates['latitude']"
            :available="$coordinates['available']"
            :decimals="5" />

        <x-sigma.parameter
            label="Koordinat (Bujur)"
            :value="$coordinates['longitude']"
            :available="$coordinates['available']"
            :decimals="5" />

        <p class="panel-note">{{ $coordinates['source'] }}</p>

    </section>

    <section class="panel priority-score-panel">

        <div class="panel-title">
            <div>
                <h3>Analisis Prioritas</h3>
                <p>Perbandingan skor risiko dan dampak wilayah ini.</p>
            </div>
        </div>

        <div class="priority-score-main">

            <x-sigma.score-value
                class="score-big"
                :value="$priority['score']"
                :scale="100"
                :decimals="2" />

            <x-sigma.priority-badge :level="$priority['level']" />

        </div>

        <p class="priority-level-note">{{ $priority['level_note'] }}</p>

        <x-sigma.parameter
            :label="$risk['source']['label_long']"
            :value="$risk['score']"
            :available="$risk['available']"
            :scale="null"
            :unit="'dari 100'"
            :decimals="0" />

        <x-sigma.parameter
            :label="$impact['source']['label_long']"
            :value="$impact['score']"
            :available="$impact['available']"
            :unit="'dari 100'"
            :decimals="0" />

        <x-sigma.parameter
            label="Kelengkapan Data"
            :value="$priority['data_completeness']"
            :available="true"
            :unit="'%'"
            :decimals="2" />

        <div class="parameter">
            <span>Dihitung Pada</span>

            @if ($priority['calculated_at'])
                <b class="parameter-value">
                    {{ $priority['calculated_at']->diffForHumans() }}
                </b>
            @else
                <x-sigma.unavailable />
            @endif
        </div>

        <div class="parameter">
            <span>Versi Metodologi</span>

            @if ($priority['methodology_version'])
                <b class="parameter-value">{{ $priority['methodology_version'] }}</b>
            @else
                <x-sigma.unavailable />
            @endif
        </div>

    </section>


{{-- ============================================================
     ANALISIS RISIKO & DAMPAK
============================================================ --}}

<div class="priority-analysis-grid">

    <section class="panel">

        <div class="panel-title">

            <div>
                <h3>{{ $risk['source']['label_long'] }}</h3>
                <p>Sumber: {{ $risk['source']['source'] }}</p>
            </div>

            @if ($risk['level'])
                <span class="status {{ $risk['level'] === 'HIGH' ? 'nonaktif' : ($risk['level'] === 'MEDIUM' ? 'pending' : 'aktif') }}">
                    Model AI: {{ $risk['level'] }}
                </span>
            @endif

        </div>

        <x-sigma.parameter
            label="Skor Risiko"
            :value="$risk['score']"
            :available="$risk['available']"
            :unit="'dari 100'"
            :decimals="0" />

        <div class="parameter">
            <span>Waktu Analisis Risiko</span>

            @if ($risk['calculated_at'])
                <b class="parameter-value">{{ $risk['calculated_at']->diffForHumans() }}</b>
            @else
                <x-sigma.unavailable />
            @endif
        </div>

        <p class="panel-note">{{ $risk['source']['source_detail'] }}</p>

    </section>

    <section class="panel">

        <div class="panel-title">

            <div>
                <h3>{{ $impact['source']['label_long'] }}</h3>
                <p>Sumber: {{ $impact['source']['source'] }}</p>
            </div>

            @if ($impact['level'])
                <span class="status pending">
                    Analisis: {{ $impact['level'] }}
                </span>
            @endif

        </div>

        <x-sigma.parameter
            label="Skor Dampak"
            :value="$impact['score']"
            :available="$impact['available']"
            :unit="'dari 100'"
            :decimals="0" />

        <x-sigma.parameter
            label="Radius Analisis"
            :value="$impact['radius_km']"
            :available="$impact['radius_km'] !== null"
            :unit="'km'"
            :decimals="2" />

        <div class="parameter">
            <span>Waktu Analisis Dampak</span>

            @if ($impact['calculated_at'])
                <b class="parameter-value">{{ $impact['calculated_at']->diffForHumans() }}</b>
            @else
                <x-sigma.unavailable />
            @endif
        </div>

        <p class="panel-note">{{ $impact['source']['source_detail'] }}</p>

    </section>

</div>


{{-- ============================================================
     FAKTOR PENYEBAB
============================================================ --}}

<div class="priority-analysis-grid">

    <section class="panel">

        <div class="panel-title">
            <div>
                <h3>Faktor Risiko</h3>
                <p>Parameter lingkungan yang dipakai model risiko.</p>
            </div>
        </div>

        @foreach ($risk['factors'] as $factor)

            <div class="parameter">
                <span>{{ $factor['label'] }}</span>

                <x-sigma.score-value
                    :value="$factor['value']"
                    :available="$factor['available']"
                    :unit="$factor['unit']"
                    :decimals="$factor['unit'] === 'NDVI' ? 3 : 2"
                    class="parameter-value" />
            </div>

            @if (! empty($factor['influence']))
                <p class="factor-note">{{ $factor['influence'] }}</p>
            @endif

        @endforeach

        <p class="panel-note">
            {{ $risk['weather']['source'] }}.
            Dataset cuaca historis (tabel weather_records)
            <b>{{ $risk['weather']['recorded_dataset_available'] ? 'tersedia' : 'belum tersedia' }}</b>.
        </p>

    </section>

    <section class="panel">

        <div class="panel-title">
            <div>
                <h3>Komponen Dampak</h3>
                <p>Kontribusi setiap komponen terhadap skor dampak.</p>
            </div>
        </div>

        @forelse ($impact['components'] as $item)

            <article class="component-item {{ $item['available'] ? '' : 'is-unavailable' }}">

                <div class="component-head">
                    <span>{{ $item['label'] }}</span>

                    <x-sigma.score-value
                        :value="$item['value']"
                        :available="$item['available']"
                        :scale="100"
                        :decimals="2" />
                </div>

                <div class="component-bar">
                    <i style="width: {{ min(max((float) ($item['value'] ?? 0), 0), 100) }}%"></i>
                </div>

                @if ($item['note'])
                    <small>{{ $item['note'] }}</small>
                @endif

            </article>

        @empty

            <p class="panel-note">
                Rincian komponen dampak belum tersedia karena wilayah ini belum
                memiliki hasil Analisis Dampak.
            </p>

        @endforelse

    </section>

</div>

{{-- ============================================================
     INDIKATOR DAMPAK
============================================================ --}}

<section class="panel">

    <div class="panel-title">
        <div>
            <h3>Indikator Dampak</h3>
            <p>Angka mentah hasil analisis dampak spasial untuk wilayah ini.</p>
        </div>
    </div>

    <div class="priority-indicator-grid">

        @foreach ($impact['indicators'] as $indicator)

            <x-sigma.parameter
                :label="$indicator['label']"
                :value="$indicator['value']"
                :available="$indicator['available']"
                :unit="$indicator['unit']"
                :decimals="in_array($indicator['unit'], ['km²', 'skor'], true) ? 2 : 0" />

        @endforeach

    </div>

</section>


{{-- ============================================================
     INTEGRASI AI RECOMMENDATION (disiapkan, belum diaktifkan)
============================================================ --}}

<div class="priority-analysis-grid">

    <section class="panel">

        <div class="panel-title">

            <div>
                <h3>Rekomendasi AI</h3>
                <p>
                    Modul prioritas menyediakan data; penyusunan rekomendasi
                    tindakan dikerjakan AI Recommendation Service.
                </p>
            </div>

            <span class="status {{ $aiOutput['available'] ? 'aktif' : 'pending' }}">
                {{ $aiOutput['available'] ? 'Tersedia' : 'Belum tersedia' }}
            </span>

        </div>

        <div class="parameter">
            <span>Rekomendasi</span>

            @if ($aiOutput['recommendation'])
                <b class="parameter-value">{{ $aiOutput['recommendation'] }}</b>
            @else
                <x-sigma.unavailable />
            @endif
        </div>

        <div class="parameter">
            <span>Tindakan Prioritas</span>

            @if ($aiOutput['priority_action'])
                <b class="parameter-value">{{ $aiOutput['priority_action'] }}</b>
            @else
                <x-sigma.unavailable />
            @endif
        </div>

        <p class="panel-note">
            Penyedia: {{ $aiOutput['provider'] ?? 'belum ditentukan' }}.
            Status: {{ $aiOutput['status'] }}.
            Modul prioritas tidak memanggil model bahasa selama
            konfigurasinya belum diaktifkan.
        </p>

    </section>

    <section class="panel">

        <div class="panel-title">
            <div>
                <h3>Data untuk AI Service</h3>
                <p>Masukan yang disiapkan modul prioritas untuk AI Service.</p>
            </div>
        </div>

        <details class="payload-details">

            <summary>Lihat struktur masukan (JSON)</summary>

            <pre>{{ json_encode($aiInput, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>

        </details>

        <p class="panel-note">
            Endpoint data satu wilayah:
            <code>{{ route('government.priority.payload', $detail['result']) }}</code>
        </p>

    </section>

</div>

{{-- ============================================================
     SUMBER DATA & METODOLOGI
============================================================ --}}

<section class="panel">

    <div class="panel-title">
        <div>
            <h3>Transparansi Sumber Data</h3>
            <p>Status ketersediaan dataset pada sistem, diperiksa langsung ke database.</p>
        </div>
    </div>

    <x-sigma.data-source-cards :sources="$detail['sources']" />

</section>

<section class="panel">

    <div class="panel-title">
        <div>
            <h3>Metodologi Perhitungan Prioritas</h3>
        </div>

        <span class="methodology-version">{{ $priority['methodology_version'] }}</span>

    </div>

    <ul class="methodology-notes">
        @foreach ($detail['methodology_notes'] as $note)
            <li>{{ $note }}</li>
        @endforeach
    </ul>

</section>

@endsection

</div>
