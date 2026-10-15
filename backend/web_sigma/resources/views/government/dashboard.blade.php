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

    @forelse($data['government_metrics'] as $metric)

        <x-sigma.stat-card
            :label="$metric['label']"
            :value="$metric['value']"
            :icon="$metric['icon']"
            :accent="$metric['accent']"
            caption="<b class='success'>Data realtime</b>"
        />

    @empty

        {{-- Tabel fire_statistics kosong: tampilkan keterangan, bukan angka contoh --}}
        <x-sigma.stat-card
            label="Statistik Hotspot"
            value="Belum tersedia"
            icon="alert"
            accent="warning"
            caption="Tabel <b>fire_statistics</b> masih kosong sehingga belum ada angka nyata untuk ditampilkan."
        />

    @endforelse

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
        <div class="monitoring-map">

    <div id="sigma-map"></div>


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

<script>

window.sigmaRegions = @json($sigmaRegions);

</script>


@push('scripts')

<script src="{{ asset('js/government/gis-map.js') }}?v={{ time() }}"></script>

@endpush
@endsection