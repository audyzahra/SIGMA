@extends('layouts.government')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/government/reports/show.css') }}">
@endpush

@section('title', 'Laporan Masyarakat | SIGMA')

@section('content')

    <section class="page-heading">

        <div>

            <p class="breadcrumb">
                Beranda › Laporan Masyarakat
            </p>

            <h1>
                Laporan Masyarakat
            </h1>

            <p>
                Daftar laporan kejadian yang dikirimkan oleh masyarakat untuk diperiksa oleh Pemerintah.
            </p>

        </div>

    </section>


    {{-- ==========================================================
        FILTER
    =========================================================== --}}

    <section class="panel filters">

        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari pelapor atau keterangan..."
            form="report-filter-form">


        <select name="status" form="report-filter-form">

            <option value="">
                Semua Status
            </option>

            <option value="pending" @selected(request('status') === 'pending')>
                Pending
            </option>

            <option value="verified" @selected(request('status') === 'verified')>
                Diterima
            </option>

            <option value="rejected" @selected(request('status') === 'rejected')>
                Ditolak
            </option>

        </select>


        <select name="report_type" form="report-filter-form">

            <option value="">
                Semua Jenis
            </option>

            <option value="fire" @selected(request('report_type') === 'fire')>
                Kebakaran
            </option>

            <option value="smoke" @selected(request('report_type') === 'smoke')>
                Asap
            </option>

            <option value="burning_activity" @selected(request('report_type') === 'burning_activity')>
                Aktivitas Pembakaran
            </option>

            <option value="other" @selected(request('report_type') === 'other')>
                Lainnya
            </option>

        </select>


        <form id="report-filter-form" method="GET" action="{{ route('government.reports.index') }}">
        </form>


        <button type="submit" form="report-filter-form" class="button button-light">
            Terapkan
        </button>


        <a href="{{ route('government.reports.index') }}" class="text-action">
            Reset Filter
        </a>

    </section>


    {{-- ==========================================================
        TABEL LAPORAN
    =========================================================== --}}

    <section class="panel priority-table">

        <x-sigma.data-table>

            <thead>

                <tr>

                    <th>No</th>

                    <th>Pelapor</th>

                    <th>Jenis Laporan</th>

                    <th>Lokasi</th>

                    <th>Tanggal</th>

                    <th>Status</th>

                    <th>Aksi</th>

                </tr>

            </thead>


            <tbody>

                @forelse($reports as $index => $report)
                    @php

                        $reportTypeLabel = match ($report->report_type) {
                            'fire' => 'Kebakaran',

                            'smoke' => 'Asap',

                            'burning_activity' => 'Aktivitas Pembakaran',

                            'other' => 'Lainnya',

                            default => ucfirst(str_replace('_', ' ', $report->report_type ?? '-')),
                        };

                        $statusLabel = match ($report->verification_status) {
                            'pending' => 'Pending',

                            'verified' => 'Diterima',

                            'rejected' => 'Ditolak',

                            default => ucfirst($report->verification_status ?? '-'),
                        };

                    @endphp


                    <tr>

                        {{-- NO --}}

                        <td>

                            <b>
                                {{ $reports->firstItem() + $index }}
                            </b>

                        </td>


                        {{-- PELAPOR --}}

                        <td>

                            <b>
                                {{ $report->user?->name ?? 'Masyarakat' }}
                            </b>

                            <small>
                                ID Laporan #{{ $report->id }}
                            </small>

                        </td>


                        {{-- JENIS --}}

                        <td>

                            <b>
                                {{ $reportTypeLabel }}
                            </b>

                        </td>


                        {{-- LOKASI --}}

                        <td>

                            <b>
                                {{ number_format((float) $report->latitude, 6) }},
                                {{ number_format((float) $report->longitude, 6) }}
                            </b>

                            <small>
                                Koordinat laporan
                            </small>

                        </td>


                        {{-- TANGGAL --}}

                        <td>

                            {{ $report->created_at?->format('d M Y') }}

                            <small>
                                {{ $report->created_at?->format('H:i') }} WIB
                            </small>

                        </td>


                        {{-- STATUS --}}

                        <td>

                            <span class="report-status {{ $report->verification_status }}">
                                {{ $statusLabel }}
                            </span>

                        </td>


                        {{-- AKSI --}}

                        <td>

                            <a href="{{ route('government.reports.show', $report) }}" class="text-action">
                                Lihat Detail
                            </a>

                        </td>

                    </tr>


                @empty

                    <tr>

                        <td colspan="7" style="text-align: center;">
                            Belum ada laporan masyarakat.
                        </td>

                    </tr>
                @endforelse

            </tbody>

        </x-sigma.data-table>


        {{-- ======================================================
            PAGINATION
        ======================================================= --}}

        @if ($reports->hasPages())
            <div style="margin-top: 20px;">
                {{ $reports->links() }}
            </div>
        @endif

    </section>

@endsection
