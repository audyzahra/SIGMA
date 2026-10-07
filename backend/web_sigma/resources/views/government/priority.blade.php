@extends('layouts.government')

@section('title', 'Prioritas Penanganan | SIGMA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/government/priority.css') }}?v={{ time() }}">
@endpush

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Data halaman
    |--------------------------------------------------------------------------
    |
    | Seluruh angka pada halaman ini berasal dari PriorityCalculationService
    | (tabel priority_results, fire_risks, impact_assessments).
    |
    | View ini TIDAK menghitung apa pun: bobot, ambang kategori, skor, filter,
    | dan pagination seluruhnya dikerjakan service + database.
    |
    */

    $levels = $options['levels'];

    $regionLevelLabels = config('sigma_priority.region_level_labels', []);

    /* Ikon kartu ringkasan per kategori prioritas */
    $levelIcons = [
        'critical' => 'fire',
        'high' => 'alert',
        'medium' => 'shield',
        'low' => 'check',
    ];

    $hasFilter = ! empty($filters['search'])
        || ! empty($filters['level'])
        || ! empty($filters['province_id'])
        || ! empty($filters['regency_id']);

    $lastCalculated = $summary['last_calculated_at'];
@endphp

{{-- ============================================================
     HEADER
============================================================ --}}

<section class="page-heading priority-heading">

    <div>
        <p class="breadcrumb">Beranda › Prioritas Penanganan</p>

        <h1>Prioritas Penanganan</h1>

        <p>
            Urutan prioritas wilayah berdasarkan tingkat risiko dan dampak.
        </p>
    </div>

    <div class="heading-actions">

        <span class="system-status">
            ● {{ $lastCalculated
                ? 'Dihitung ' . $lastCalculated->diffForHumans()
                : 'Belum pernah dihitung' }}
        </span>

        {{-- Filter yang sedang aktif dipertahankan setelah perhitungan ulang --}}
        <form method="POST"
            action="{{ route('government.priority.recalculate') }}"
            class="inline-form"
            data-priority-recalculate>

            @csrf

            @foreach (['search', 'level', 'province_id', 'regency_id'] as $field)
                @if (! empty($filters[$field]))
                    <input type="hidden" name="{{ $field }}" value="{{ $filters[$field] }}">
                @endif
            @endforeach

            <button type="submit" class="button button-primary">
                Hitung Ulang Prioritas
            </button>

        </form>

    </div>

</section>

{{-- ============================================================
     SUMMARY CARD (dihitung dari database)
============================================================ --}}

<div class="stats-grid priority-stats">

    <x-sigma.stat-card
        label="Total Wilayah"
        :value="number_format($summary['total_regions'], 0, ',', '.')"
        icon="location"
        accent="orange"
        caption="Provinsi, kabupaten, dan kecamatan pada cakupan sistem." />

    @foreach ($levels as $key => $level)

        <x-sigma.stat-card
            :label="'Wilayah ' . $level['label']"
            :value="number_format($summary['levels'][$key] ?? 0, 0, ',', '.')"
            :icon="$levelIcons[$key] ?? 'default'"
            :accent="$level['accent']"
            :caption="$level['note']" />

    @endforeach

    <x-sigma.stat-card
        label="Belum Dianalisis"
        :value="number_format($summary['not_analyzed'], 0, ',', '.')"
        icon="map"
        accent="info"
        caption="Belum memiliki data risiko maupun dampak." />

</div>

{{-- ============================================================
     FILTER (semua dikerjakan sebagai query database)
============================================================ --}}

<section class="panel priority-filter">

    <div class="panel-title">

        <div>
            <h3>Cari &amp; Saring Wilayah</h3>

            <p>
                Pencarian dan penyaringan dijalankan pada query database,
                bukan penyaringan di sisi klien.
            </p>
        </div>

        @if ($hasFilter)
            <a class="text-action" href="{{ route('government.priority') }}">
                Reset Filter
            </a>
        @endif

    </div>

    <form method="GET"
        action="{{ route('government.priority') }}"
        class="priority-filter-form">

        <label>
            <span>Cari Wilayah</span>

            <input type="search"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="Nama wilayah atau wilayah induknya..." />
        </label>

        <label>
            <span>Tingkat Prioritas</span>

            <select name="level">
                <option value="">Semua Prioritas</option>

                @foreach ($levels as $key => $level)
                    <option value="{{ $key }}" @selected($filters['level'] === $key)>
                        {{ $level['label'] }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span>Provinsi</span>

            <select name="province_id"
                id="priority-province"
                data-region-children-url="{{ url('/regions') }}">
                <option value="">Semua Provinsi</option>

                @foreach ($options['provinces'] as $province)
                    <option value="{{ $province->id }}" @selected($filters['province_id'] === (int) $province->id)>
                        {{ $province->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span>Kabupaten / Kota</span>

            <select name="regency_id"
                id="priority-regency"
                @disabled($options['regencies']->isEmpty())>
                <option value="">
                    {{ $options['regencies']->isEmpty()
                        ? 'Pilih provinsi terlebih dahulu'
                        : 'Semua Kabupaten / Kota' }}
                </option>

                @foreach ($options['regencies'] as $regency)
                    <option value="{{ $regency->id }}" @selected($filters['regency_id'] === (int) $regency->id)>
                        {{ $regency->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <div class="priority-filter-actions">
            <button type="submit" class="button button-primary">
                Terapkan Filter
            </button>
        </div>

    </form>

</section>

{{-- ============================================================
     RANKING TABLE (priority_score DESC, pagination dari database)
============================================================ --}}

<section class="panel priority-table">

    <div class="panel-title">

        <div>
            <h3>Ranking Wilayah</h3>

            <p>
                Diurutkan dari Priority Score tertinggi. Priority Score =
                (Risk Score x {{ round($weights['risk'] * 100) }}%) +
                (Impact Score x {{ round($weights['impact'] * 100) }}%).
            </p>
        </div>

    </div>

    <x-sigma.data-table class="priority-rank-table">

        <thead>

            <tr>
                <th>Ranking</th>
                <th>Wilayah</th>
                <th>Risiko</th>
                <th>Dampak</th>
                <th>Priority Score</th>
                <th>Status Prioritas</th>
                <th>Aksi</th>
            </tr>

        </thead>

        <tbody>

            @forelse ($priorities as $priority)

                <tr>

                    <td class="rank-cell">
                        <b>
                            {{-- Peringkat nasional dari engine; cadangan: urutan halaman --}}
                            {{ $priority->ranking_position ?? ($priorities->firstItem() + $loop->index) }}
                        </b>
                    </td>

                    <td class="region-cell">
                        <b>{{ $priority->region_name }}</b>

                        <small>
                            {{ $regionLevelLabels[$priority->region_level] ?? ucfirst((string) $priority->region_level) }}

                            @if ($priority->parent_region_name)
                                • {{ $priority->parent_region_name }}
                            @endif
                        </small>
                    </td>

                    <td>
                        <x-sigma.score-value
                            :value="$priority->risk_score"
                            :scale="100"
                            :decimals="0" />
                    </td>

                    <td>
                        <x-sigma.score-value
                            :value="$priority->impact_score"
                            :scale="100"
                            :decimals="0" />
                    </td>

                    <td class="score-cell">
                        <x-sigma.score-value
                            :value="$priority->priority_score"
                            :scale="100"
                            :decimals="2" />
                    </td>

                    <td class="level-cell">

                        <x-sigma.priority-badge :level="$priority->priority_level" />

                        <small>
                            Kelengkapan data
                            {{ number_format((float) $priority->data_completeness, 0) }}%
                        </small>

                    </td>

                    <td>

                        <a class="text-action"
                            href="{{ route('government.priority.show', $priority) }}">
                            Lihat Detail
                        </a>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7" class="empty-cell">

                        <b>
                            @if ($hasFilter)
                                Tidak ada wilayah yang cocok dengan filter ini.
                            @else
                                Belum ada hasil prioritas penanganan.
                            @endif
                        </b>

                        <small>
                            @if ($hasFilter)
                                Ubah atau reset filter untuk melihat wilayah lain.
                            @else
                                Jalankan "Hitung Ulang Prioritas" setelah data risiko
                                (AI Service) atau analisis dampak tersedia.
                            @endif
                        </small>

                    </td>

                </tr>

            @endforelse

        </tbody>

    </x-sigma.data-table>

    <x-pagination :paginator="$priorities" />

</section>


{{-- ============================================================
     TRANSPARANSI SUMBER DATA
============================================================ --}}

<section class="panel priority-sources-panel">

    <div class="panel-title">

        <div>
            <h3>Transparansi Sumber Data</h3>

            <p>
                Status ketersediaan diperiksa langsung ke database, sehingga
                dataset yang belum ada dilaporkan apa adanya.
            </p>
        </div>

    </div>

    <x-sigma.data-source-cards :sources="$dataSources" />

</section>

{{-- ============================================================
     METODOLOGI
============================================================ --}}

<section class="panel">

    <div class="panel-title">

        <div>
            <h3>Metodologi Perhitungan Prioritas</h3>
        </div>

    </div>

    <div class="priority-level-legend">

        @foreach ($options['levels'] as $key => $level)
            <span class="legend-item">
                <x-sigma.priority-badge :level="$key" />
                <small>{{ $level['threshold'] }} ke atas</small>
            </span>
        @endforeach

    </div>

    <ul class="methodology-notes">
        @foreach ($methodologyNotes as $note)
            <li>{{ $note }}</li>
        @endforeach
    </ul>

    <p class="priority-detail-note">
        Halaman ini menjawab "wilayah mana yang harus ditangani lebih dahulu".
        Risiko (AI Service) menjawab "seberapa besar peluang terjadinya",
        Analisis Dampak menjawab "apa yang terdampak", dan Rekomendasi menyusun
        tindakan yang disarankan.
    </p>

</section>

@push('scripts')
<script src="{{ asset('js/government/priority.js') }}?v={{ time() }}"></script>
@endpush

@endsection


