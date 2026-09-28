@extends('layouts.government')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/government/dashboard.css') }}">
@endpush

@section('title', 'Dashboard Monitoring Karhutla | SIGMA')

@section('content')

<div class="dashboard-page">

<section class="page-heading">
    <div>
        <p class="breadcrumb">Beranda › Dashboard</p>

        <h1>Dashboard Monitoring Karhutla</h1>

        <p>
            Pantauan kondisi karhutla nasional secara realtime.
        </p>
    </div>

    <span class="system-status">
        ● Data diperbarui
        {{ $statistics?->statistic_date?->diffForHumans() ?? 'belum tersedia' }}
    </span>
</section>


{{-- ============================================================
     STATISTIK UTAMA
============================================================ --}}

<div class="stats-grid government-stats">

    @foreach($data['government_metrics'] as $metric)

        <x-sigma.stat-card
            :label="$metric['label']"
            :value="$metric['value']"
            :icon="$metric['icon']"
            :accent="$metric['accent']"
            caption="<b class='success'>Data realtime</b>"
        />

    @endforeach

</div>


{{-- ============================================================
     MONITORING & AKTIVITAS
============================================================ --}}

<div class="government-grid">

    {{-- =========================
         PETA MONITORING
    ========================== --}}
    <section class="panel">

        <div class="panel-title">

            <div>
                <h3>Peta Monitoring</h3>

                <p>
                    Hotspot, zona risiko, dan area kejadian
                </p>
            </div>

            <button
                class="button button-light"
                data-toast="Layer peta diperbarui"
            >
                ⌖ Layer Peta
            </button>

        </div>


        {{-- Map --}}
        <div class="map-placeholder monitoring-map">

            <div class="map-controls">
                ＋
                <hr>
                −
            </div>


            {{-- Placeholder wilayah --}}
            <div class="island island-one"></div>

            <div class="island island-two">
                <i></i>
                <i></i>
            </div>

            <div class="island island-three"></div>

            <div class="island island-four"></div>


            {{-- Legend --}}
            <div class="map-legend">

                <span class="danger">
                    ● Hotspot
                </span>

                <span class="orange-text">
                    ● Zona Risiko
                </span>

                <span class="warning">
                    ● Area Kejadian
                </span>

                <span>
                    □ Batas Wilayah
                </span>

            </div>

        </div>

    </section>


    {{-- =========================
         AKTIVITAS TERBARU
    ========================== --}}
    <section class="panel activity-panel">

        <h3>
            Aktivitas Terbaru
        </h3>


        @foreach($data['recent_activities'] ?? [] as $activity)

            <div class="gov-activity">

                <time>
                    {{ $activity['time'] }}
                </time>

                <i class="{{ $activity['type'] }}">
                    ●
                </i>

                <b>
                    {{ $activity['label'] }}
                </b>

            </div>

        @endforeach


        <button
            class="button button-light"
            data-modal="activity-modal"
        >
            Lihat Semua Aktivitas
        </button>

    </section>

</div>


{{-- ============================================================
     MODAL AKTIVITAS
============================================================ --}}

<x-sigma.modal
    id="activity-modal"
    title="Aktivitas Terkini"
>


<div class="table-wrap">

    <table class="data-table">

        <thead>

            <tr>

                <th>
                    Waktu
                </th>

                <th>
                    Status
                </th>

                <th>
                    Aktivitas
                </th>

            </tr>

        </thead>


        <tbody>

        @foreach($data['recent_activities'] ?? [] as $activity)

            <tr>

                <td>
                    {{ $activity['time'] }}
                </td>


                <td>

                    <span class="status {{ $activity['type'] }}">
                        {{ ucfirst($activity['type']) }}
                    </span>

                </td>


                <td>
                    {{ $activity['label'] }}
                </td>


            </tr>

        @endforeach


        </tbody>


    </table>

</div>

</x-sigma.modal>

</div>
@endsection